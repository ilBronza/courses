<?php

namespace IlBronza\Courses\Tests\Feature;

use IlBronza\Courses\Helpers\Certificates\CertificateLogo;
use IlBronza\Courses\Helpers\Certificates\CourseCertificatePdfHelper;
use IlBronza\Courses\Helpers\Certificates\Sources\OperatorCourseSessionCertificateData;
use IlBronza\Courses\Models\Course;
use IlBronza\Courses\Models\CourseSession;
use IlBronza\Courses\Models\OperatorCourseSession;
use IlBronza\Courses\Tests\TestCase;
use Illuminate\Support\Facades\File;

class CertificateLogoTest extends TestCase
{
	private string $directory;

	protected function setUp() : void
	{
		parent::setUp();
		$this->directory = sys_get_temp_dir() . '/certificate-logo-' . bin2hex(random_bytes(6));
		File::ensureDirectoryExists($this->directory . '/images');
		copy(CertificateLogo::getDefaultPath(), $this->directory . '/images/app.jpg');
		$this->app->usePublicPath($this->directory);
		config(['app.logo' => null, 'uikittemplate.logo' => null, 'menu.logo.path' => null]);
	}

	protected function tearDown() : void
	{
		File::deleteDirectory($this->directory);
		parent::tearDown();
	}

	private function edition(?Course $course = null) : CourseSession
	{
		return (new CourseSession)->forceFill(['id' => 'edition', 'course_theme_id' => 'theme'])
			->setRelation('course', $course ?? (new Course)->forceFill(['alias' => 'FL.G']));
	}

	public function test_logo_precedence_and_empty_overrides_keep_the_package_before_the_application() : void
	{
		$edition = $this->edition();
		$package = CertificateLogo::getDefaultPath();
		$this->assertSame([697, 137], array_slice(getimagesize($package), 0, 2));
		config(['app.logo' => $this->directory . '/images/app.jpg']);
		$this->assertSame($package, $edition->getCertificateLogo());
		config(['courses.certificates.courses' => ['FL.G' => ['logo' => '/course.jpg']]]);
		$this->assertSame('/course.jpg', $edition->getCertificateLogo());
		config(['courses.certificates.themes' => ['theme' => ['logo' => '/theme.jpg']]]);
		$this->assertSame('/theme.jpg', $edition->getCertificateLogo());
		config(['courses.certificates.sessions' => ['edition' => ['logo' => '/edition.jpg']]]);
		$this->assertSame('/edition.jpg', $edition->getCertificateLogo());
		config(['courses.certificates.sessions.edition.logo' => ' ']);
		$this->assertSame('/theme.jpg', $edition->getCertificateLogo());
		config(['courses.certificates.themes.theme.logo' => null]);
		$this->assertSame('/course.jpg', $edition->getCertificateLogo());
		config(['courses.certificates.courses' => []]);
		$this->assertSame($package, $edition->getCertificateLogo());
		config(['courses.certificates.logo' => null]);
		$this->assertSame($this->directory . '/images/app.jpg', $edition->getCertificateLogo());
	}

	public function test_missing_package_file_falls_back_to_existing_application_assets() : void
	{
		config(['courses.certificates.logo' => '/missing-package-logo.jpg']);
		config(['uikittemplate.logo' => '/images/app.jpg']);
		$this->assertSame(realpath($this->directory . '/images/app.jpg'), CertificateLogo::getDefaultPath());
		config(['uikittemplate.logo' => null, 'menu.logo.path' => 'images/app.jpg']);
		$this->assertSame(realpath($this->directory . '/images/app.jpg'), CertificateLogo::getDefaultPath());
		config(['app.url' => 'https://ecostudio.test', 'menu.logo.path' => 'https://ecostudio.test/images/app.jpg?v=2']);
		$this->assertSame(realpath($this->directory . '/images/app.jpg'), CertificateLogo::getDefaultPath());
		config(['menu.logo.path' => 'https://elsewhere.test/images/app.jpg']);
		$this->assertNull(CertificateLogo::getDefaultPath());
		config(['menu.logo.path' => '/missing-app-logo.jpg']);
		$this->assertNull(CertificateLogo::getDefaultPath());
	}

	public function test_application_data_uri_is_preserved_and_missing_parents_use_the_default() : void
	{
		$uri = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($this->directory . '/images/app.jpg'));
		config(['courses.certificates.logo' => '', 'app.logo' => $uri]);
		$this->assertSame($uri, (new CourseSession)->setRelation('course', null)->getCertificateLogo());
	}

	public function test_model_getters_and_the_native_adapter_are_honored_by_the_helper() : void
	{
		$course = new class extends Course {
			public function getCertificateLogo() : ?string { return '/custom-course.jpg'; }
		};
		$this->assertSame('/custom-course.jpg', $this->edition($course)->getCertificateLogo());
		$edition = new class extends CourseSession {
			public function getCertificateLogo() : ?string { return '/custom-edition.jpg'; }
		};
		$edition->setRelation('course', (new Course)->forceFill(['alias' => 'TEST']))
			->setRelation('dateSessions', collect())->setRelation('teachers', collect());
		$participation = (new OperatorCourseSession)->setRelation('courseSession', $edition)->setRelation('operator', null);
		$source = new OperatorCourseSessionCertificateData($participation);
		$this->assertSame('/custom-edition.jpg', $source->getCertificateLogo());
		$this->assertSame('/custom-edition.jpg', (new CourseCertificatePdfHelper($participation))->getCertificateLogo());
		$this->assertSame('/explicit.jpg', (new CourseCertificatePdfHelper($source, ['logo' => '/explicit.jpg']))->getCertificateLogo());
	}
}
