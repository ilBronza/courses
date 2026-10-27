<?php

namespace IlBronza\Courses\Http\Controllers\OperatorCourseSessions;

use IlBronza\Courses\Helpers\Certificates\CourseCertificatePdfHelper;
use IlBronza\Courses\Mail\CourseCertificatesMail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\HeaderUtils;
use ZipArchive;

class OperatorCourseSessionCertificateController extends OperatorCourseSessionCRUD
{
	public $allowedMethods = ['showCertificate', 'downloadCertificates', 'sendCertificates'];

	protected function selectedParticipations(Request $request) : Collection
	{
		$data = $request->validate([
			'ids' => 'required|array|min:1',
			'ids.*' => 'required|uuid|distinct',
		], [
			'ids.required' => 'Seleziona almeno una partecipazione nella tabella.',
			'ids.min' => 'Seleziona almeno una partecipazione nella tabella.',
			'ids.array' => 'La selezione delle partecipazioni non è valida. Ricarica la tabella e riprova.',
			'ids.*.required' => 'La selezione contiene una partecipazione vuota. Ricarica la tabella e riprova.',
			'ids.*.uuid' => 'La selezione contiene una partecipazione non valida. Ricarica la tabella e riprova.',
			'ids.*.distinct' => 'La stessa partecipazione è stata selezionata più volte.',
		]);
		$participations = $this->getModelClass()::with([
			'operator.userdata', 'courseSession.course', 'courseSession.dateSessions', 'courseSession.teachers',
		])->whereKey($data['ids'])->get();

		if($participations->count() !== count($data['ids']))
			throw ValidationException::withMessages(['ids' => 'Una o più partecipazioni selezionate non sono disponibili.']);

		foreach($participations as $participation)
			abort_unless($participation->userCanSee($request->user()), 403);

		return $participations;
	}

	protected function certificates(Collection $participations) : array
	{
		$helperClass = config('courses.helpers.certificates', CourseCertificatePdfHelper::class);
		$helpers = [];
		$errors = [];
		foreach($participations as $participation)
		{
			$context = ($participation->operator?->getName() ?: 'Operatore ' . $participation->operator_id)
				. ' — ' . ($participation->courseSession?->name ?: 'Partecipazione ' . $participation->getKey());
			try
			{
				$helper = new $helperClass($participation);
				// Validate every selected participation before generating files or sending mail.
				$helper->getViewData();
				$helper->getFilePath();
				$helpers[$participation->getKey()] = $helper;
			}
			catch(ValidationException $exception)
			{
				foreach($exception->errors() as $field => $messages)
					foreach($messages as $message)
						$errors[$field][] = $context . ': ' . $message;
			}
			catch(InvalidArgumentException $exception)
			{
				$errors['certificates'][] = $context . ': ' . $exception->getMessage();
			}
		}
		if($errors)
			throw ValidationException::withMessages($errors);

		$certificates = [];
		foreach($helpers as $id => $helper)
			$certificates[$id] = ['name' => basename($helper->getFilePath()), 'content' => $helper->generate()];

		return $certificates;
	}

	public function showCertificate(Request $request, string $operatorCourseSession)
	{
		$participation = $this->getModelClass()::with([
			'operator.userdata', 'courseSession.course', 'courseSession.dateSessions', 'courseSession.teachers',
		])->findOrFail($operatorCourseSession);
		abort_unless($participation->userCanSee($request->user()), 403);

		try
		{
			$certificate = $this->certificates(new Collection([$participation]))[$participation->getKey()];
		}
		catch(ValidationException $exception)
		{
			// An iframe must display the error instead of redirecting back to the index.
			abort(422, collect($exception->errors())->flatten()->implode(' '));
		}

		return response($certificate['content'], 200, [
			'Content-Type' => 'application/pdf',
			'Content-Disposition' => HeaderUtils::makeDisposition('inline', $certificate['name'], str_replace('%', '', Str::ascii($certificate['name']))),
		]);
	}

	public function downloadCertificates(Request $request)
	{
		$certificates = $this->certificates($this->selectedParticipations($request));
		if(count($certificates) === 1)
		{
			$certificate = reset($certificates);
			return response($certificate['content'], 200, [
				'Content-Type' => 'application/pdf',
				'Content-Disposition' => HeaderUtils::makeDisposition('attachment', $certificate['name'], str_replace('%', '', Str::ascii($certificate['name']))),
			]);
		}

		$directory = config('courses.certificates.temp_directory') ?: storage_path('app/temp/course-certificates');
		File::ensureDirectoryExists($directory);
		$path = tempnam($directory, 'attestati-');
		if($path === false)
			throw new RuntimeException('Impossibile creare il file ZIP degli attestati.');

		try
		{
			$zip = new ZipArchive();
			if($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true)
				throw new RuntimeException('Impossibile aprire il file ZIP degli attestati.');

			try
			{
				$names = [];
				foreach($certificates as $id => $certificate)
				{
					$name = $certificate['name'];
					if(isset($names[$name]))
						$name = $id . '-' . $name;
					$names[$name] = true;
					if(! $zip->addFromString($name, $certificate['content']))
						throw new RuntimeException('Impossibile aggiungere un attestato allo ZIP.');
				}
			}
			finally
			{
				if(! $zip->close())
					throw new RuntimeException('Impossibile completare il file ZIP degli attestati.');
			}

			return response()->download($path, 'attestati-' . now()->format('Ymd_His') . '.zip', [
				'Content-Type' => 'application/zip',
			])->deleteFileAfterSend(true);
		}
		catch(\Throwable $exception)
		{
			File::delete($path);
			throw $exception;
		}
	}

	public function sendCertificates(Request $request)
	{
		$participations = $this->selectedParticipations($request);
		$recipients = [];
		foreach($participations as $participation)
		{
			$email = trim((string) $participation->operator?->getEmail());
			if(! filter_var($email, FILTER_VALIDATE_EMAIL))
				throw ValidationException::withMessages(['certificates' => 'Email principale mancante o non valida per l’operatore ' . ($participation->operator?->getName() ?: $participation->operator_id) . '.']);
			$recipients[$participation->operator_id] = $email;
		}
		$certificates = $this->certificates($participations);

		$sentCertificates = $sentOperators = 0;
		$errors = [];
		foreach($participations->groupBy('operator_id') as $operatorId => $group)
			try
			{
				Mail::to($recipients[$operatorId])->send(new CourseCertificatesMail(
					$group->first()->operator->getName() ?? '',
					$group->map(fn ($participation) => $certificates[$participation->getKey()])->all(),
				));
				$sentCertificates += $group->count();
				$sentOperators++;
			}
			catch(\Throwable $exception)
			{
				report($exception);
				$errors[] = 'Invio non riuscito a ' . $recipients[$operatorId] . '.';
			}

		$message = null;
		if($sentOperators > 0)
			$message = trans_choice('courses::courses.certificatesSent', $sentCertificates, [
				'recipients' => trans_choice('courses::courses.certificateRecipients', $sentOperators),
			]);
		if($request->expectsJson())
			return response()->json([
				'message' => $message,
				'errors' => $errors ? ['certificates' => $errors] : (object) [],
			], $sentOperators === 0 && $errors ? 502 : 200);

		$response = back();
		if($message !== null)
			$response->with('success', $message);
		return $errors ? $response->withErrors(['certificates' => $errors]) : $response;
	}
}
