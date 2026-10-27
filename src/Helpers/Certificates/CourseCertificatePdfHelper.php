<?php

namespace IlBronza\Courses\Helpers\Certificates;

use Dompdf\Dompdf;
use Dompdf\Options;
use IlBronza\Courses\Contracts\CourseCertificateData;
use IlBronza\Courses\Contracts\HasCertificateBackground;
use IlBronza\Courses\Helpers\Certificates\Sources\LegacyCourseWorkerCertificateData;
use IlBronza\Courses\Helpers\Certificates\Sources\OperatorCourseSessionCertificateData;
use IlBronza\Courses\Models\OperatorCourseSession;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Generic, usable implementation; optional course helpers extend this class. */
class CourseCertificatePdfHelper implements CourseCertificateData, HasCertificateBackground
{
	protected CourseCertificateData $source;
	protected ?self $courseHelper = null;

	public function __construct(object $participation, protected array $overrides = [])
	{
		$this->source = $participation instanceof CourseCertificateData
			? $participation : ($participation instanceof OperatorCourseSession
				? new OperatorCourseSessionCertificateData($participation)
				: new LegacyCourseWorkerCertificateData($participation));

		// A child may call parent::__construct() without resolving itself again.
		if(static::class !== self::class)
			return;

		$helpers = config('courses.models.course.helpers.certificates', []);
		if(! is_array($helpers))
			throw new InvalidArgumentException('La configurazione degli helper attestati deve essere una mappa alias => classe.');

		$class = $helpers[$this->getCourseAlias()] ?? null;
		if($class === null || $class === self::class)
			return;

		if(! is_string($class) || ! is_subclass_of($class, self::class) || ! (new ReflectionClass($class))->isInstantiable())
			throw new InvalidArgumentException('L’helper attestato configurato deve essere una classe concreta che estende ' . self::class . '.');

		$this->courseHelper = new $class($this->source, $this->overrides);
	}

	public function getSource() : CourseCertificateData
	{
		return $this->source;
	}

	public function getResolvedHelper() : self
	{
		return $this->courseHelper ?? $this;
	}

	public function getCourseAlias() : string
	{
		return $this->source->getCourseAlias();
	}

	protected function sectionData(string $section, array $data) : array
	{
		$profiles = config('courses.certificates.courses', []);

		// Lists (logos, notes, signatures) are replaced, never merged by index.
		return array_replace(
			config('courses.certificates.defaults.' . $section, []),
			$data,
			$profiles[$this->getCourseAlias()][$section] ?? [],
			$this->overrides[$section] ?? [],
		);
	}

	public function getHeaderData() : array
	{
		return $this->courseHelper ? $this->courseHelper->getHeaderData() : $this->sectionData('header', $this->source->getHeaderData());
	}

	public function getParticipantData() : array
	{
		return $this->courseHelper ? $this->courseHelper->getParticipantData() : $this->sectionData('participant', $this->source->getParticipantData());
	}

	public function getCourseData() : array
	{
		return $this->courseHelper ? $this->courseHelper->getCourseData() : $this->sectionData('course', $this->source->getCourseData());
	}

	public function getDisclaimerData() : array
	{
		if($this->courseHelper)
			return $this->courseHelper->getDisclaimerData();

		$data = $this->sectionData('disclaimer', $this->source->getDisclaimerData());
		$data['issued_on'] ??= now()->toDateString();

		return $data;
	}

	public function getSignaturesData() : array
	{
		if($this->courseHelper)
			return $this->courseHelper->getSignaturesData();

		$profiles = config('courses.certificates.courses', []);

		return $this->overrides['signatures'] ?? $profiles[$this->getCourseAlias()]['signatures']
			?? config('courses.certificates.defaults.signatures') ?? $this->source->getSignaturesData();
	}

	public function getFilePath() : string
	{
		return $this->courseHelper ? $this->courseHelper->getFilePath() : $this->source->getFilePath();
	}

	public function getViewName() : string
	{
		return $this->courseHelper ? $this->courseHelper->getViewName() : config('courses.certificates.view', 'courses::certificates.default');
	}

	public function getCertificateBackground() : string
	{
		if($this->courseHelper)
			return $this->courseHelper->getCertificateBackground();

		return CertificateBackground::normalize($this->overrides['background'] ?? null)
			?? ($this->source instanceof HasCertificateBackground
				? $this->source->getCertificateBackground()
				: CertificateBackground::forCourseAlias($this->getCourseAlias()));
	}

	public function getViewData() : array
	{
		if($this->courseHelper)
			return $this->courseHelper->getViewData();

		$data = [
			'alias' => $this->getCourseAlias(),
			'background' => $this->getCertificateBackground(),
			'header' => $this->getHeaderData(),
			'participant' => $this->getParticipantData(),
			'course' => $this->getCourseData(),
			'disclaimer' => $this->getDisclaimerData(),
			'signatures' => $this->getSignaturesData(),
		];
		$this->validateData($data);
		$data['background'] = $this->imageSource($data['background']);

		foreach($data['header']['organizations'] as &$organization)
			$organization['logo'] = $this->imageSource($organization['logo'] ?? null);
		unset($organization);
		foreach($data['signatures'] as &$signature)
			$signature['image'] = $this->imageSource($signature['image'] ?? null);
		unset($signature);

		return $data;
	}

	protected function validateData(array $data) : void
	{
		Validator::make($data, [
			'alias' => 'required|string',
			'background' => 'required|string',
			'header.title' => 'nullable|string|max:255',
			'header.subtitle' => 'nullable|string',
			'header.organizations' => 'present|array',
			'header.organizations.*.name' => 'required|string|max:255',
			'header.organizations.*.logo' => 'nullable|string',
			'participant.name' => 'required|string|max:255',
			'participant.tax_code' => 'nullable|string|max:64',
			'participant.company' => 'nullable|string|max:255',
			'participant.role' => 'nullable|string|max:255',
			'participant.ateco' => 'nullable|string|max:255',
			'course.title' => 'required|string|max:500',
			'course.hours' => 'required|numeric|gt:0',
			'course.delivery_mode' => 'nullable|string|max:255',
			'course.classification' => 'nullable|string|max:255',
			'course.starts_on' => 'nullable|date_format:Y-m-d',
			'course.ends_on' => 'required|date_format:Y-m-d' . (! empty($data['course']['starts_on']) ? '|after_or_equal:course.starts_on' : ''),
			'course.location' => 'nullable|string|max:500',
			'course.dates' => 'present|array',
			'course.dates.*.label' => 'required|string',
			'course.dates.*.date' => 'required|date_format:Y-m-d',
			'disclaimer.paragraphs' => 'present|array',
			'disclaimer.paragraphs.*' => 'required|string',
			'disclaimer.assessment' => 'nullable|string',
			'disclaimer.notes' => 'present|array',
			'disclaimer.notes.*' => 'required|string',
			'disclaimer.place' => 'nullable|string|max:255',
			'disclaimer.issued_on' => 'nullable|date_format:Y-m-d',
			'signatures' => 'present|array',
			'signatures.*.name' => 'required|string|max:255',
			'signatures.*.role' => 'nullable|string|max:255',
			'signatures.*.organization' => 'nullable|string|max:255',
			'signatures.*.image' => 'nullable|string',
		], [
			'required' => 'Il campo «:attribute» è obbligatorio per generare l’attestato.',
			'present' => 'Manca il campo «:attribute» nei dati dell’attestato.',
			'string' => 'Il campo «:attribute» deve contenere un testo.',
			'array' => 'Il campo «:attribute» deve contenere un elenco.',
			'numeric' => 'Il campo «:attribute» deve contenere un numero.',
			'gt.numeric' => 'Il campo «:attribute» deve essere maggiore di :value.',
			'max.string' => 'Il campo «:attribute» non può superare :max caratteri.',
			'date_format' => 'Il campo «:attribute» deve contenere una data valida nel formato AAAA-MM-GG.',
			'after_or_equal' => 'La data di completamento non può precedere la data di inizio del corso.',
			'alias.required' => 'Il corso non ha un alias: completa la scheda del corso.',
			'participant.name.required' => 'Manca il nome dell’operatore: completa la sua anagrafica.',
			'course.ends_on.required' => 'Manca la data di completamento della partecipazione: compilala prima di scaricare o inviare l’attestato.',
		], [
			'alias' => 'alias del corso',
			'background' => 'sfondo dell’attestato',
			'header.title' => 'titolo dell’attestato',
			'header.subtitle' => 'sottotitolo dell’attestato',
			'header.organizations' => 'enti dell’attestato',
			'header.organizations.*.name' => 'nome dell’ente',
			'header.organizations.*.logo' => 'logo dell’ente',
			'participant.name' => 'nome dell’operatore',
			'participant.tax_code' => 'codice fiscale',
			'participant.company' => 'azienda',
			'participant.role' => 'mansione',
			'participant.ateco' => 'codice ATECO',
			'course.title' => 'titolo del corso',
			'course.hours' => 'ore del corso',
			'course.delivery_mode' => 'modalità di erogazione',
			'course.classification' => 'classificazione del corso',
			'course.starts_on' => 'data di inizio del corso',
			'course.ends_on' => 'data di completamento',
			'course.location' => 'sede del corso',
			'course.dates' => 'date delle lezioni',
			'course.dates.*.label' => 'nome della lezione',
			'course.dates.*.date' => 'data della lezione',
			'disclaimer.paragraphs' => 'testi dell’attestato',
			'disclaimer.paragraphs.*' => 'testo dell’attestato',
			'disclaimer.assessment' => 'esito della verifica',
			'disclaimer.notes' => 'note dell’attestato',
			'disclaimer.notes.*' => 'nota dell’attestato',
			'disclaimer.place' => 'luogo di emissione',
			'disclaimer.issued_on' => 'data di emissione',
			'signatures' => 'firme',
			'signatures.*.name' => 'nome del firmatario',
			'signatures.*.role' => 'ruolo del firmatario',
			'signatures.*.organization' => 'ente del firmatario',
			'signatures.*.image' => 'immagine della firma',
		])->validate();
	}

	protected function imageSource(?string $path) : ?string
	{
		if($path === null || $path === '')
			return null;

		if(preg_match('#^data:image/(png|jpeg|gif);base64,#', $path))
			return $path;

		if(! is_file($path) || ! is_readable($path))
			throw new InvalidArgumentException('Immagine attestato inesistente o non leggibile: ' . $path);

		$info = getimagesize($path);
		if(! $info || ! in_array($info['mime'], ['image/png', 'image/jpeg', 'image/gif'], true))
			throw new InvalidArgumentException('Lo sfondo, i loghi e le firme devono essere immagini PNG, JPEG o GIF.');

		return 'data:' . $info['mime'] . ';base64,' . base64_encode(file_get_contents($path));
	}

	public function renderHtml() : string
	{
		if($this->courseHelper)
			return $this->courseHelper->renderHtml();

		return view($this->getViewName(), $this->getViewData())->render();
	}

	public function generate() : string
	{
		if($this->courseHelper)
			return $this->courseHelper->generate();

		$directory = config('courses.certificates.temp_directory') ?: storage_path('app/temp/course-certificates');
		File::ensureDirectoryExists($directory);
		$options = new Options([
			'defaultFont' => 'DejaVu Sans',
			'tempDir' => $directory,
			'fontCache' => $directory,
			'isRemoteEnabled' => false,
			'isPhpEnabled' => false,
			'isJavascriptEnabled' => false,
		]);
		$pdf = new Dompdf($options);
		$pdf->setPaper('A4', 'landscape');
		$pdf->loadHtml($this->renderHtml(), 'UTF-8');
		$pdf->render();

		if($pdf->getCanvas()->get_page_count() !== 1)
			throw new RuntimeException('L’attestato supera una pagina A4: ridurre i contenuti o personalizzare il template.');

		return $pdf->output();
	}

	/** Generate and store the PDF; return the path exposed by the data contract. */
	public function save() : string
	{
		if($this->courseHelper)
			return $this->courseHelper->save();

		$path = $this->getFilePath();
		$content = $this->generate();
		File::ensureDirectoryExists(dirname($path));
		File::replace($path, $content);
		clearstatcache(true, $path);

		if(! is_file($path) || filesize($path) !== strlen($content))
			throw new RuntimeException('Salvataggio attestato fallito: ' . $path);

		return $path;
	}

	public function download() : BinaryFileResponse
	{
		if($this->courseHelper)
			return $this->courseHelper->download();

		return response()->download($this->save(), basename($this->getFilePath()), ['Content-Type' => 'application/pdf']);
	}
}
