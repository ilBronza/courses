<?php

namespace IlBronza\Courses\Tests\Feature;

use IlBronza\Courses\Helpers\Certificates\CertificateBackground;
use IlBronza\Courses\Helpers\Certificates\Sources\OperatorCourseSessionCertificateData;
use IlBronza\Courses\Models\Course;
use IlBronza\Courses\Models\CourseSession;
use IlBronza\Courses\Models\OperatorCourseSession;
use IlBronza\Courses\Tests\TestCase;

class CertificateBackgroundTest extends TestCase
{
	private function edition(?Course $course = null) : CourseSession
	{
		$course ??= (new Course)->forceFill(['alias' => 'FL.G']);

		return (new CourseSession)->forceFill(['id' => 'edition', 'course_theme_id' => 'theme'])
			->setRelation('course', $course);
	}

	public function test_resolves_edition_theme_course_application_and_bundled_backgrounds_in_order() : void
	{
		$edition = $this->edition();
		$bundled = CertificateBackground::getDefaultPath();
		$this->assertFileExists($bundled);
		$this->assertSame('image/jpeg', getimagesize($bundled)['mime']);
		$this->assertSame($bundled, $edition->getCertificateBackground());

		config(['courses.certificates.background' => '/application.jpg']);
		$this->assertSame('/application.jpg', $edition->getCertificateBackground());
		config(['courses.certificates.courses' => ['FL.G' => ['background' => '/course.jpg']]]);
		$this->assertSame('/course.jpg', $edition->getCertificateBackground());
		config(['courses.certificates.themes' => ['theme' => ['background' => '/theme.jpg']]]);
		$this->assertSame('/theme.jpg', $edition->getCertificateBackground());
		config(['courses.certificates.sessions' => ['edition' => ['background' => '/edition.jpg']]]);
		$this->assertSame('/edition.jpg', $edition->getCertificateBackground());

		config(['courses.certificates.sessions.edition.background' => '  ']);
		$this->assertSame('/theme.jpg', $edition->getCertificateBackground());
		config(['courses.certificates.themes.theme.background' => null]);
		$this->assertSame('/course.jpg', $edition->getCertificateBackground());
		config(['courses.certificates.courses' => []]);
		$this->assertSame('/application.jpg', $edition->getCertificateBackground());
		config(['courses.certificates.background' => '']);
		$this->assertSame($bundled, $edition->getCertificateBackground());
	}

	public function test_edition_calls_the_course_getter_and_keeps_using_soft_deleted_courses() : void
	{
		$course = new class extends Course {
			public function getCertificateBackground() : string { return '/custom-course.jpg'; }
		};
		$this->assertSame('/custom-course.jpg', $this->edition($course)->getCertificateBackground());

		$stored = Course::forceCreate(['name' => 'Corso storico', 'alias' => 'HISTORY', 'hours' => 4]);
		$stored->delete();
		config(['courses.certificates.courses' => ['HISTORY' => ['background' => '/history.jpg']]]);
		$edition = (new CourseSession)->forceFill(['course_id' => $stored->getKey()]);
		$this->assertSame('/history.jpg', $edition->getCertificateBackground());
	}

	public function test_missing_course_inherits_the_default_and_session_override_does_not_need_relations() : void
	{
		$edition = (new CourseSession)->forceFill(['id' => 'edition'])->setRelation('course', null);
		$this->assertSame(CertificateBackground::getDefaultPath(), $edition->getCertificateBackground());
		config(['courses.certificates.sessions' => ['edition' => ['background' => '/edition.jpg']]]);
		$this->assertSame('/edition.jpg', $edition->getCertificateBackground());
	}

	public function test_native_participation_adapter_uses_the_edition_getter() : void
	{
		$edition = new class extends CourseSession {
			public function getCertificateBackground() : string { return '/custom-edition.jpg'; }
		};
		$edition->setRelation('course', new Course)->setRelation('dateSessions', collect())->setRelation('teachers', collect());
		$participation = (new OperatorCourseSession)->setRelation('courseSession', $edition)->setRelation('operator', null);
		$this->assertSame('/custom-edition.jpg', (new OperatorCourseSessionCertificateData($participation))->getCertificateBackground());
	}
}
