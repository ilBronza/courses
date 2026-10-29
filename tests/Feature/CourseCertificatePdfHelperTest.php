<?php

namespace IlBronza\Courses\Tests\Feature;

use IlBronza\Courses\Contracts\CourseCertificateData;
use IlBronza\Courses\CoursesServiceProvider;
use IlBronza\Courses\Helpers\Certificates\CourseCertificatePdfHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase;

class CourseCertificatePdfHelperTest extends TestCase
{
	protected string $directory;

	protected function getPackageProviders($app)
	{
		return [CoursesServiceProvider::class];
	}

	protected function defineEnvironment($app)
	{
		$app['config']->set('database.default', 'testing');
		foreach(['testing', 'old'] as $name)
			$app['config']->set('database.connections.' . $name, ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
	}

	protected function setUp() : void
	{
		parent::setUp();
		$this->directory = sys_get_temp_dir() . '/course-certificates-test-' . bin2hex(random_bytes(6));
		config(['courses.certificates.temp_directory' => $this->directory]);

		Schema::connection('old')->create('courses', function (Blueprint $table)
		{
			$table->id(); $table->string('alias'); $table->string('name'); $table->decimal('hours', 5, 2);
		});
		Schema::connection('old')->create('workers', function (Blueprint $table)
		{
			$table->id(); $table->string('first_name'); $table->string('second_name'); $table->string('fiscal_code'); $table->string('ateco')->nullable();
		});
		Schema::connection('old')->create('companies', function (Blueprint $table)
		{
			$table->id(); $table->string('name');
		});
		Schema::connection('old')->create('company_worker', function (Blueprint $table)
		{
			$table->id(); $table->integer('company_id'); $table->integer('worker_id'); $table->string('job'); $table->string('actual_job')->nullable(); $table->softDeletes();
		});
		DB::connection('old')->table('courses')->insert(['id' => 6, 'alias' => 'PS_A', 'name' => 'Aggiornamento addetto al primo soccorso', 'hours' => 4]);
		DB::connection('old')->table('workers')->insert(['id' => 1, 'first_name' => 'Mario', 'second_name' => 'Rossi', 'fiscal_code' => 'RSSMRA80A01H501U', 'ateco' => '47.73.10']);
		DB::connection('old')->table('companies')->insert(['id' => 42, 'name' => 'Azienda esempio']);
		DB::connection('old')->table('company_worker')->insert(['company_id' => 42, 'worker_id' => 1, 'job' => 'Magazziniere']);
	}

	protected function tearDown() : void
	{
		File::deleteDirectory($this->directory);
		parent::tearDown();
	}

	protected function participation(array $attributes = []) : object
	{
		return (object) array_replace([
			'id' => 25, 'course_id' => 6, 'worker_id' => 1, 'company_id' => 42,
			'hidden_notes' => 'CODICE_ATTESTATO', 'completion' => '2026-09-30',
			'subscription_date' => '2026-01-01', 'completion_ps_t1' => '2026-09-29',
			'e_learning' => false, 'venue_string' => 'Via Esempio 2, Milano', 'teacher_string' => 'Docente esempio',
			'notes' => 'ANNOTAZIONE RISERVATA',
		], $attributes);
	}

	public function test_extracts_from_old_without_using_the_default_catalog_or_private_notes() : void
	{
		Schema::create('courses', function (Blueprint $table) { $table->id(); $table->string('alias'); });
		DB::table('courses')->insert(['id' => 6, 'alias' => 'WRONG_DATABASE']);
		$helper = new CourseCertificatePdfHelper($this->participation());
		$this->assertSame($helper, $helper->getResolvedHelper());
		$this->assertSame('PS_A', $helper->getCourseAlias());
		$this->assertSame('Rossi Mario', $helper->getParticipantData()['name']);
		$this->assertSame('Azienda esempio', $helper->getParticipantData()['company']);
		$this->assertSame('Magazziniere', $helper->getParticipantData()['role']);
		$this->assertNull($helper->getCourseData()['starts_on']);
		$html = $helper->renderHtml();
		$this->assertStringContainsString('29/09/2026', $html);
		$this->assertStringNotContainsString('ANNOTAZIONE RISERVATA', $html);
		$this->assertStringNotContainsString('esito positivo', $html);
		$this->assertStringNotContainsString('01/01/2026', $html);
	}

	public function test_accepts_an_eloquent_row_without_using_its_relations() : void
	{
		$model = new class extends Model {
			protected $guarded = [];
			public function course() { throw new \LogicException('Must not use the new catalog relation.'); }
		};
		$model->forceFill((array) $this->participation());
		$this->assertSame('PS_A', (new CourseCertificatePdfHelper($model))->getCourseAlias());
	}

	public function test_constructor_delegates_to_the_configured_subclass_without_recursion() : void
	{
		config(['courses.models.course.helpers.certificates' => ['PS_A' => CustomCertificateHelper::class]]);
		$helper = new CourseCertificatePdfHelper($this->participation());
		$this->assertInstanceOf(CustomCertificateHelper::class, $helper->getResolvedHelper());
		$this->assertSame($helper->getSource(), $helper->getResolvedHelper()->getSource());
		$this->assertSame('Titolo personalizzato', $helper->getCourseData()['title']);
		$this->assertStringContainsString('Titolo personalizzato', $helper->renderHtml());
		$this->assertSame($this->directory . '/CODICE_ATTESTATO.pdf', $helper->getFilePath());
	}

	public function test_rejects_invalid_helper_mappings() : void
	{
		foreach(['MissingCertificateHelper', \stdClass::class, AbstractCertificateHelper::class] as $class)
		{
			config(['courses.models.course.helpers.certificates' => ['PS_A' => $class]]);
			try {
				new CourseCertificatePdfHelper($this->participation());
				$this->fail('Invalid mapping accepted: ' . $class);
			} catch (InvalidArgumentException $exception) {
				$this->assertStringContainsString('classe concreta', $exception->getMessage());
			}
		}
	}

	public function test_future_contract_needs_no_legacy_database() : void
	{
		$source = new class($this->directory) implements CourseCertificateData {
			public function __construct(private string $directory) {}
			public function getCourseAlias() : string { return 'FUTURE'; }
			public function getHeaderData() : array { return []; }
			public function getParticipantData() : array { return ['name' => 'Persona futura']; }
			public function getCourseData() : array { return ['title' => 'Corso futuro', 'hours' => 2.5, 'ends_on' => '2026-10-01']; }
			public function getDisclaimerData() : array { return []; }
			public function getSignaturesData() : array { return []; }
			public function getFilePath() : string { return $this->directory . '/future.pdf'; }
		};
		DB::purge('old');
		config(['database.connections.old' => null]);
		$helper = new CourseCertificatePdfHelper($source);
		$this->assertSame($source, $helper->getSource());
		$this->assertStringContainsString('2,5 ore', $helper->renderHtml());
		$this->assertSame($this->directory . '/future.pdf', $helper->getFilePath());
		$this->assertStringStartsWith('data:image/jpeg;base64,', $helper->getViewData()['background']);
		$this->assertStringStartsWith('data:image/jpeg;base64,', $helper->getViewData()['logo']);
	}

	public function test_legacy_background_inherits_alias_and_explicit_override_wins() : void
	{
		$helper = new CourseCertificatePdfHelper($this->participation());
		$this->assertStringStartsWith('data:image/jpeg;base64,', $helper->getViewData()['background']);
		$png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a4v8AAAAASUVORK5CYII=';
		config(['courses.certificates.courses' => ['PS_A' => ['background' => $png]]]);
		$this->assertSame($png, $helper->getCertificateBackground());
		$this->assertSame($png, $helper->getViewData()['background']);
		$this->assertStringContainsString('class="certificate-background" src="' . $png . '"', $helper->renderHtml());

		$explicit = (new CourseCertificatePdfHelper($this->participation(), ['background' => '/explicit.jpg']));
		$this->assertSame('/explicit.jpg', $explicit->getCertificateBackground());
		config(['courses.models.course.helpers.certificates' => ['PS_A' => CustomCertificateHelper::class]]);
		$this->assertSame('/explicit.jpg', (new CourseCertificatePdfHelper($this->participation(), ['background' => '/explicit.jpg']))->getCertificateBackground());
	}

	public function test_invalid_background_fails_instead_of_silently_generating_a_missing_frame() : void
	{
		$helper = new CourseCertificatePdfHelper($this->participation(), ['background' => $this->directory . '/missing.jpg']);
		try {
			$helper->save();
			$this->fail('Missing background accepted.');
		} catch (InvalidArgumentException $exception) {
			$this->assertStringContainsString('Immagine attestato inesistente', $exception->getMessage());
			$this->assertFileDoesNotExist($helper->getFilePath());
		}
	}

	public function test_legacy_logo_is_embedded_with_partner_logos_and_honors_alias_and_explicit_overrides() : void
	{
		$helper = new CourseCertificatePdfHelper($this->participation(), ['header' => ['organizations' => [['name' => 'Partner']]]]);
		$default = $helper->getCertificateLogo();
		$this->assertStringStartsWith('data:image/jpeg;base64,', $helper->getViewData()['logo']);
		$html = $helper->renderHtml();
		$this->assertStringContainsString('class="certificate-logo"', $html);
		$this->assertStringContainsString('Partner', $html);
		config(['courses.certificates.courses' => ['PS_A' => ['logo' => '/course.jpg']]]);
		$this->assertSame('/course.jpg', $helper->getCertificateLogo());
		config(['courses.models.course.helpers.certificates' => ['PS_A' => CustomCertificateHelper::class]]);
		$this->assertSame($default, (new CourseCertificatePdfHelper($this->participation(), ['logo' => $default]))->getCertificateLogo());
	}

	public function test_absent_package_and_application_logos_leave_no_broken_image() : void
	{
		config(['courses.certificates.logo' => null, 'app.logo' => null, 'uikittemplate.logo' => null, 'menu.logo.path' => null]);
		$helper = new CourseCertificatePdfHelper($this->participation());
		$this->assertNull($helper->getCertificateLogo());
		$this->assertNull($helper->getViewData()['logo']);
		$this->assertStringNotContainsString('class="certificate-logo"', $helper->renderHtml());
		$this->assertStringStartsWith('%PDF-', $helper->generate());
	}

	public function test_invalid_explicit_logo_fails_before_writing_a_certificate() : void
	{
		$helper = new CourseCertificatePdfHelper($this->participation(), ['logo' => $this->directory . '/missing.jpg']);
		try {
			$helper->save();
			$this->fail('Missing explicit logo accepted.');
		} catch (InvalidArgumentException $exception) {
			$this->assertStringContainsString('Immagine attestato inesistente', $exception->getMessage());
			$this->assertFileDoesNotExist($helper->getFilePath());
		}
	}

	public function test_filename_preserves_the_code_and_rejects_paths() : void
	{
		foreach(['Nome originale.PDF' => 'Nome originale.PDF', "Nota precedente\nCODICE_FINALE\n" => 'CODICE_FINALE.pdf'] as $notes => $filename)
			$this->assertSame($this->directory . '/' . $filename, (new CourseCertificatePdfHelper($this->participation(['hidden_notes' => $notes])))->getFilePath());

		foreach(['', '../fuori', 'cartella/file', 'cartella\\file'] as $notes)
		{
			try {
				(new CourseCertificatePdfHelper($this->participation(['hidden_notes' => $notes])))->getFilePath();
				$this->fail('Invalid filename accepted.');
			} catch (InvalidArgumentException $exception) {
				$this->assertNotEmpty($exception->getMessage());
			}
		}
	}

	public function test_incomplete_participation_does_not_create_a_file() : void
	{
		$helper = new CourseCertificatePdfHelper($this->participation(['completion' => null]));
		try {
			$helper->save();
			$this->fail('Incomplete attendance accepted.');
		} catch (ValidationException $exception) {
			$this->assertArrayHasKey('course.ends_on', $exception->errors());
			$this->assertFileDoesNotExist($helper->getFilePath());
		}
	}

	public function test_generates_and_saves_a_real_pdf_with_four_signatures_and_three_organizations() : void
	{
		File::ensureDirectoryExists($this->directory);
		$image = $this->directory . '/image.png';
		file_put_contents($image, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a4v8AAAAASUVORK5CYII='));
		$overrides = [
			'header' => ['organizations' => [['name' => 'Ente organizzatore', 'logo' => $image], ['name' => 'Ente patrocinante'], ['name' => 'Ente erogatore']]],
			'course' => ['title' => 'FORMAZIONE LAVORATORI SULLA SICUREZZA PARTE SPECIFICA', 'delivery_mode' => 'e-learning', 'starts_on' => '2026-09-01'],
			'disclaimer' => ['place' => 'Colceresa', 'issued_on' => '2026-10-19', 'paragraphs' => ['Testo normativo configurato per il corso.'], 'assessment' => 'Verifica finale superata con esito positivo.'],
			'signatures' => array_map(fn ($role) => ['name' => 'Firmatario esempio', 'role' => $role, 'image' => $image], ['Soggetto erogatore', 'Responsabile di contenuto', 'Tutor di processo', 'Docente']),
		];
		$helper = new CourseCertificatePdfHelper($this->participation(), $overrides);
		$this->assertStringContainsString('data:image/png;base64,', $helper->renderHtml());
		$path = $helper->save();
		$this->assertSame($helper->getFilePath(), $path);
		$content = file_get_contents($path);
		$this->assertStringStartsWith('%PDF-', $content);
		$this->assertMatchesRegularExpression('/\/MediaBox\s*\[\s*0(?:\.0+)?\s+0(?:\.0+)?\s+841\.\d+\s+595\.\d+\s*\]/', $content);
		$this->assertSame($path, $helper->save());
		$response = $helper->download();
		$this->assertSame('application/pdf', $response->headers->get('Content-Type'));
		$this->assertStringContainsString('CODICE_ATTESTATO.pdf', $response->headers->get('Content-Disposition'));
	}

	public function test_overrides_replace_lists_and_escape_document_content() : void
	{
		config(['courses.certificates.defaults.header.organizations' => [['name' => 'Primo'], ['name' => 'Secondo']]]);
		$helper = new CourseCertificatePdfHelper($this->participation(), [
			'header' => ['organizations' => [['name' => '<script>esempio</script>']]],
			'signatures' => [],
		]);
		$this->assertCount(1, $helper->getHeaderData()['organizations']);
		$this->assertSame([], $helper->getSignaturesData());
		$this->assertStringContainsString('&lt;script&gt;esempio&lt;/script&gt;', $helper->renderHtml());
	}

	public function test_rejects_invalid_dates_instead_of_normalizing_them() : void
	{
		$this->expectException(ValidationException::class);
		(new CourseCertificatePdfHelper($this->participation(['completion' => '2026-02-31'])))->renderHtml();
	}
}

class CustomCertificateHelper extends CourseCertificatePdfHelper
{
	public function getCourseData() : array
	{
		return array_replace(parent::getCourseData(), ['title' => 'Titolo personalizzato']);
	}
}

abstract class AbstractCertificateHelper extends CourseCertificatePdfHelper
{
}
