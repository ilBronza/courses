<?php

namespace IlBronza\Courses\Tests\Feature;

use IlBronza\AccountManager\Models\User;
use IlBronza\Courses\Helpers\Certificates\CourseCertificatePdfHelper;
use IlBronza\Courses\Mail\CourseCertificatesMail;
use IlBronza\Courses\Models\Course;
use IlBronza\Courses\Models\CourseSession;
use IlBronza\Courses\Models\OperatorCourseSession;
use IlBronza\Courses\Tests\TestCase;
use IlBronza\Operators\Models\Operator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ZipArchive;

class OperatorCourseSessionCertificateTest extends TestCase
{
	private string $directory;

	protected function defineEnvironment($app)
	{
		parent::defineEnvironment($app);
		$app['config']->set('operators.models.operator', [
			'class' => CertificateTestOperator::class,
			'table' => 'operators__operators',
		]);
	}

	protected function setUp() : void
	{
		parent::setUp();
		Mail::fake();
		$this->directory = sys_get_temp_dir() . '/course-actions-' . Str::uuid();
		config(['courses.certificates.temp_directory' => $this->directory]);
		Schema::table('users', fn (Blueprint $table) => $table->softDeletes());
		Schema::create(config('operators.models.operator.table'), function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->unsignedBigInteger('user_id')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
		foreach([
			'2026_10_19_000024_create_courses__operator_course_sessions_table.php',
			'2026_10_20_000025_add_client_id_to_courses__operator_course_sessions_table.php',
		] as $filename)
			(require __DIR__ . '/../../database/migrations/' . $filename)->up();
	}

	protected function tearDown() : void
	{
		File::deleteDirectory($this->directory);
		parent::tearDown();
	}

	private function operator(string $email = 'mario@example.test') : CertificateTestOperator
	{
		$user = User::create(['name' => 'Mario Rossi', 'email' => $email, 'password' => 'secret']);
		$id = (string) Str::uuid();
		DB::table(config('operators.models.operator.table'))->insert(['id' => $id, 'user_id' => $user->getKey()]);
		return CertificateTestOperator::findOrFail($id);
	}

	private function participation(?CertificateTestOperator $operator = null) : OperatorCourseSession
	{
		$course = Course::forceCreate(['name' => 'Formazione lavoratori', 'alias' => 'FL_G', 'hours' => 99]);
		$type = $course->dates()->create(['name' => 'Teoria', 'hours' => '4.00']);
		$theme = $course->themes()->create(['name' => 'Farmacia', 'program' => [
			['date_id' => $type->getKey(), 'name' => 'Lezione di teoria', 'hours' => '4.00'],
		]]);
		$edition = CourseSession::create([
			'course_theme_id' => $theme->getKey(), 'name' => 'Edizione ottobre', 'desired_start_date' => '2026-01-01',
		]);
		$edition->dateSessions->first()->update(['starts_at' => '2026-10-19 09:00:00']);
		return $edition->operatorCourseSessions()->create([
			'operator_id' => ($operator ?? $this->operator())->getKey(),
			'completed_at' => '2026-10-19 13:00:00',
			'note' => 'ANNOTAZIONE RISERVATA',
		]);
	}

	private function action(string $action) : string
	{
		return route('ibCourses.operatorCourseSessions.' . $action . 'Certificates');
	}

	public function test_single_selection_downloads_a_real_pdf_using_edition_data() : void
	{
		$this->withoutExceptionHandling();
		$participation = $this->participation();
		$helper = new CourseCertificatePdfHelper($participation);
		$data = $helper->getViewData();
		$this->assertSame('Mario Rossi', $data['participant']['name']);
		$this->assertSame(4.0, (float) $data['course']['hours']);
		$this->assertSame('2026-10-19', $data['course']['starts_on']);
		$this->assertSame('2026-10-19', $data['course']['ends_on']);
		$this->assertStringNotContainsString('ANNOTAZIONE RISERVATA', $helper->renderHtml());

		$response = $this->postJson($this->action('download'), ['ids' => [$participation->getKey()]], ['X-Requested-With' => 'XMLHttpRequest'])
			->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertHeaderMissing('Location');
		$this->assertStringStartsWith('%PDF-', $response->getContent());
		$this->assertStringContainsString($participation->getKey() . '.pdf', $response->headers->get('Content-Disposition'));
		Mail::assertNothingSent();
	}

	public function test_multiple_selections_download_a_zip_with_every_pdf() : void
	{
		$first = $this->participation();
		$second = $this->participation($first->operator);
		$response = $this->postJson($this->action('download'), ['ids' => [$first->getKey(), $second->getKey()]], ['X-Requested-With' => 'XMLHttpRequest'])
			->assertOk()->assertHeader('Content-Type', 'application/zip')->assertHeaderMissing('Location');
		$path = $response->baseResponse->getFile()->getPathname();
		$zip = new ZipArchive();
		$this->assertTrue($zip->open($path));
		$this->assertSame(2, $zip->numFiles);
		foreach([$first, $second] as $participation)
		{
			$name = basename((new CourseCertificatePdfHelper($participation))->getFilePath());
			$this->assertStringStartsWith('%PDF-', $zip->getFromName($name));
		}
		$zip->close();
		$this->assertCount(1, array_filter(File::files($this->directory), fn ($file) => str_starts_with($file->getFilename(), 'attestati-')));
		ob_start();
		try {
			$response->baseResponse->sendContent();
		} finally {
			ob_end_clean();
		}
		$this->assertFileDoesNotExist($path);
	}

	public function test_sending_groups_attachments_and_uses_only_each_operators_primary_email() : void
	{
		$first = $this->participation();
		$second = $this->participation($first->operator);
		$third = $this->participation($this->operator('anna@example.test'));
		$index = route('ibCourses.operatorCourseSessions.index');
		$this->from($index)->post($this->action('send'), [
			'ids' => [$first->getKey(), $second->getKey(), $third->getKey()],
			'email' => 'ignored@example.test',
		])->assertRedirect($index)->assertSessionHas('success', 'Inviati 3 attestati a 2 operatori.');

		Mail::assertSentCount(2);
		Mail::assertSent(CourseCertificatesMail::class, function ($mail) use ($first, $second)
		{
			if(! $mail->hasTo('mario@example.test'))
				return false;
			$this->assertCount(2, $mail->certificates);
			$this->assertStringContainsString('Gentile Mario Rossi', $mail->render());
			$mail->build();
			foreach([$first, $second] as $participation)
			{
				$name = basename((new CourseCertificatePdfHelper($participation))->getFilePath());
				$attachment = collect($mail->rawAttachments)->firstWhere('name', $name);
				$this->assertSame('application/pdf', $attachment['options']['mime']);
				$this->assertStringStartsWith('%PDF-', $attachment['data']);
			}
			return true;
		});
		Mail::assertSent(CourseCertificatesMail::class, fn ($mail) => $mail->hasTo('anna@example.test') && count($mail->certificates) === 1);
		Mail::assertNotSent(CourseCertificatesMail::class, fn ($mail) => $mail->hasTo('ignored@example.test'));
		$this->get($index)->assertOk()->assertSee('Inviati 3 attestati a 2 operatori.');
	}

	public function test_mail_failure_is_reported_while_other_recipients_are_still_sent_their_certificates() : void
	{
		$first = $this->participation();
		$second = $this->participation($this->operator('anna@example.test'));
		$failed = \Mockery::mock();
		$failed->shouldReceive('send')->once()->andThrow(new \RuntimeException('Mail transport unavailable'));
		$sent = \Mockery::mock();
		$sent->shouldReceive('send')->once()->with(\Mockery::on(fn ($mail) => count($mail->certificates) === 1));
		Mail::shouldReceive('to')->with('mario@example.test')->once()->andReturn($failed);
		Mail::shouldReceive('to')->with('anna@example.test')->once()->andReturn($sent);
		$index = route('ibCourses.operatorCourseSessions.index');
		$this->from($index)->post($this->action('send'), ['ids' => [$first->getKey(), $second->getKey()]])
			->assertRedirect($index)->assertSessionHasErrors('certificates')
			->assertSessionHas('success', 'Inviato 1 attestato a 1 operatore.');
		$this->get($index)->assertOk()->assertSee('Invio non riuscito a mario@example.test.');
	}

	public function test_missing_primary_email_prevents_the_entire_batch_from_sending() : void
	{
		$valid = $this->participation();
		$invalid = $this->participation($this->operator(''));
		$this->postJson($this->action('send'), ['ids' => [$valid->getKey(), $invalid->getKey()]])
			->assertUnprocessable()->assertJsonValidationErrors('certificates');
		Mail::assertNothingSent();
		$this->assertDirectoryDoesNotExist($this->directory);
	}

	public function test_incomplete_participation_prevents_download_and_email_before_any_side_effect() : void
	{
		$valid = $this->participation();
		$incomplete = $this->participation($valid->operator);
		$incomplete->update(['completed_at' => null]);
		foreach(['download', 'send'] as $action)
			$this->postJson($this->action($action), ['ids' => [$valid->getKey(), $incomplete->getKey()]])
				->assertUnprocessable()->assertJsonValidationErrors('course.ends_on');
		Mail::assertNothingSent();
		$this->assertDirectoryDoesNotExist($this->directory);
	}

	public function test_actions_reject_empty_invalid_duplicate_and_deleted_selections() : void
	{
		$deleted = $this->participation();
		$deleted->delete();
		foreach(['download', 'send'] as $action)
			foreach([[], ['not-a-uuid'], [(string) Str::uuid()], [$deleted->getKey()], [$deleted->getKey(), $deleted->getKey()]] as $ids)
				$this->postJson($this->action($action), ['ids' => $ids])->assertUnprocessable();
		Mail::assertNothingSent();
	}

	public function test_ajax_empty_selection_explains_how_to_select_participations_without_redirecting() : void
	{
		foreach(['download', 'send'] as $action)
			$this->postJson($this->action($action), ['ids' => []], ['X-Requested-With' => 'XMLHttpRequest'])
				->assertUnprocessable()->assertHeaderMissing('Location')
				->assertJsonPath('errors.ids.0', 'Seleziona almeno una partecipazione nella tabella.')
				->assertDontSee('validation.required');
	}

	public function test_ajax_certificate_errors_identify_missing_fields_and_each_affected_edition() : void
	{
		$first = $this->participation();
		$second = $this->participation($first->operator);
		$first->update(['completed_at' => null]);
		$first->courseSession->course->forceFill(['alias' => null])->save();
		$second->update(['completed_at' => null]);
		$second->courseSession->update(['name' => 'Edizione novembre']);
		$response = $this->postJson($this->action('download'), ['ids' => [$first->getKey(), $second->getKey()]], ['X-Requested-With' => 'XMLHttpRequest'])
			->assertUnprocessable()->assertHeaderMissing('Location')
			->assertJsonValidationErrors(['alias', 'course.ends_on'])->assertDontSee('validation.required');
		$this->assertCount(2, $response->json('errors')['course.ends_on']);
		$messages = implode(' ', $response->json('errors')['course.ends_on']);
		$this->assertStringContainsString('Mario Rossi — Edizione ottobre: Manca la data di completamento', $messages);
		$this->assertStringContainsString('Mario Rossi — Edizione novembre: Manca la data di completamento', $messages);
		$this->assertStringContainsString('Il corso non ha un alias', $response->json('errors.alias.0'));
		$this->assertDirectoryDoesNotExist($this->directory);
		Mail::assertNothingSent();
	}

	public function test_ajax_send_returns_feedback_without_redirecting() : void
	{
		$participation = $this->participation();
		$this->postJson($this->action('send'), ['ids' => [$participation->getKey()]], ['X-Requested-With' => 'XMLHttpRequest'])
			->assertOk()->assertHeaderMissing('Location')
			->assertJsonPath('message', 'Inviato 1 attestato a 1 operatore.')
			->assertExactJson(['message' => 'Inviato 1 attestato a 1 operatore.', 'errors' => []]);
		Mail::assertSentCount(1);
	}

	public function test_ajax_mail_failure_returns_the_recipient_error_without_redirecting() : void
	{
		$participation = $this->participation();
		$failed = \Mockery::mock();
		$failed->shouldReceive('send')->once()->andThrow(new \RuntimeException('Mail transport unavailable'));
		Mail::shouldReceive('to')->with('mario@example.test')->once()->andReturn($failed);
		$this->postJson($this->action('send'), ['ids' => [$participation->getKey()]], ['X-Requested-With' => 'XMLHttpRequest'])
			->assertStatus(502)->assertHeaderMissing('Location')
			->assertJsonPath('errors.certificates.0', 'Invio non riuscito a mario@example.test.');
	}

	public function test_actions_require_post_and_the_course_role() : void
	{
		$participation = $this->participation();
		foreach(['download', 'send'] as $action)
			$this->get($this->action($action))->assertStatus(405);
		auth()->user()->syncRoles([]);
		foreach(['download', 'send'] as $action)
			$this->postJson($this->action($action), ['ids' => [$participation->getKey()]])->assertForbidden();
		Mail::assertNothingSent();
	}
}

class CertificateTestOperator extends Operator
{
	protected $with = [];

	public function getName() : ?string
	{
		return $this->getUser()?->name;
	}
}
