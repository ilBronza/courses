<?php

namespace IlBronza\Courses\Tests\Feature;

use IlBronza\Courses\Helpers\Certificates\CertificateAssets;
use IlBronza\Courses\Helpers\Certificates\Sources\OperatorCourseSessionCertificateData;
use IlBronza\Courses\Models\Course;
use IlBronza\Courses\Models\CourseSession;
use IlBronza\Courses\Models\OperatorCourseSession;
use IlBronza\Courses\Models\Teacher;
use IlBronza\Courses\Tests\TestCase;
use Illuminate\Support\Facades\File;

class CertificateAssetsTest extends TestCase
{
	private string $directory;

	protected function setUp() : void
	{
		parent::setUp();
		$this->directory = sys_get_temp_dir() . '/certificate-assets-' . bin2hex(random_bytes(6));
		foreach(['package', 'app'] as $root)
			foreach(['signatures', 'organizations'] as $type)
				File::ensureDirectoryExists($this->directory . '/' . $root . '/' . $type);
		config(['courses.certificates.asset_paths' => [$this->directory . '/package', $this->directory . '/app']]);
	}

	protected function tearDown() : void
	{
		File::deleteDirectory($this->directory);
		parent::tearDown();
	}

	public function test_package_precedes_application_and_missing_assets_leave_no_image() : void
	{
		$package = $this->directory . '/package/signatures/anna-rigon.png';
		$app = $this->directory . '/app/signatures/anna-rigon.png';
		$organization = $this->directory . '/app/organizations/aine-servizi.png';
		touch($package); touch($app); touch($organization);
		$this->assertSame($package, CertificateAssets::getTeacherSignaturePath('Anna Rigon'));
		$this->assertSame($organization, CertificateAssets::getOrganizationLogoPath('Aine Servizi'));
		unlink($package);
		$this->assertSame($app, (new Teacher(['name' => 'Anna Rigon']))->getSignatureImagePath());
		$this->assertNull(CertificateAssets::getTeacherSignaturePath('Rigon Anna'));
		$this->assertNull(CertificateAssets::getTeacherSignaturePath('Dott.ssa Anna Rigon'));
		$this->assertNull(CertificateAssets::getOrganizationLogoPath('Anna Rigon'));
		$this->assertNull(CertificateAssets::getTeacherSignaturePath('../'));
		$this->assertNull(CertificateAssets::getOrganizationLogoPath('Ente senza logo'));
	}

	public function test_native_adapter_honors_teacher_and_course_getters() : void
	{
		$teacher = new class extends Teacher {
			public function getSignatureImagePath() : ?string { return '/custom-signature.png'; }
		};
		$teacher->name = 'Anna Rigon';
		$course = new class extends Course {
			public function getCertificateOrganizations() : array { return [['name' => 'Aine Servizi']]; }
		};
		$edition = (new CourseSession)->setRelation('course', $course)
			->setRelation('dateSessions', collect())->setRelation('teachers', collect([$teacher]));
		$participation = (new OperatorCourseSession)->setRelation('operator', null)->setRelation('courseSession', $edition);
		$source = new OperatorCourseSessionCertificateData($participation);
		$this->assertSame([['name' => 'Anna Rigon', 'role' => 'Docente del corso', 'image' => '/custom-signature.png']], $source->getSignaturesData());
		$this->assertSame(['organizations' => [['name' => 'Aine Servizi']]], $source->getHeaderData());
	}
}
