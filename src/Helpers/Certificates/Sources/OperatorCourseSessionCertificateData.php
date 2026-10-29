<?php

namespace IlBronza\Courses\Helpers\Certificates\Sources;

use IlBronza\Courses\Contracts\CourseCertificateData;
use IlBronza\Courses\Contracts\HasCertificateBackground;
use IlBronza\Courses\Contracts\HasCertificateLogo;
use IlBronza\Courses\Helpers\Certificates\CertificateBackground;
use IlBronza\Courses\Helpers\Certificates\CertificateLogo;
use IlBronza\Courses\Models\OperatorCourseSession;
use IlBronza\Courses\Traits\Certificates\ResolvesCertificateFilePath;
use Illuminate\Support\Str;

class OperatorCourseSessionCertificateData implements CourseCertificateData, HasCertificateBackground, HasCertificateLogo
{
	use ResolvesCertificateFilePath;

	public function __construct(protected OperatorCourseSession $participation)
	{
		$this->participation->loadMissing(['operator', 'courseSession.course', 'courseSession.dateSessions', 'courseSession.teachers']);
	}

	public function getCourseAlias() : string
	{
		return trim((string) $this->participation->courseSession?->course?->alias);
	}

	public function getCertificateBackground() : string
	{
		return $this->participation->courseSession?->getCertificateBackground()
			?? CertificateBackground::getDefaultPath();
	}

	public function getCertificateLogo() : ?string
	{
		return $this->participation->courseSession?->getCertificateLogo()
			?? CertificateLogo::getDefaultPath();
	}

	public function getHeaderData() : array
	{
		$organizations = $this->participation->courseSession?->course?->getCertificateOrganizations() ?? [];

		return $organizations === [] ? [] : ['organizations' => $organizations];
	}

	public function getParticipantData() : array
	{
		return [
			'name' => $this->participation->operator?->getName(),
			'tax_code' => $this->participation->operator?->userdata?->fiscal_code,
		];
	}

	public function getCourseData() : array
	{
		$edition = $this->participation->courseSession;
		$course = $edition?->course;
		$lessons = $edition?->dateSessions ?? collect();
		$scheduled = $lessons->filter(fn ($lesson) => $lesson->starts_at !== null)->sortBy('starts_at');

		return [
			'title' => $course?->name,
			'hours' => $lessons->isNotEmpty() ? $lessons->sum('hours') : $course?->hours,
			'delivery_mode' => $course?->e_learning ? 'e-learning' : null,
			'starts_on' => $scheduled->first()?->starts_at?->toDateString(),
			'ends_on' => $this->participation->completed_at?->toDateString(),
			'dates' => $scheduled->map(fn ($lesson) => [
				'label' => $lesson->name, 'date' => $lesson->starts_at->toDateString(),
			])->values()->all(),
		];
	}

	public function getDisclaimerData() : array
	{
		return [];
	}

	public function getSignaturesData() : array
	{
		return ($this->participation->courseSession?->teachers ?? collect())
			->map(fn ($teacher) => [
				'name' => $teacher->name,
				'role' => 'Docente del corso',
				'image' => $teacher->getSignatureImagePath(),
			])->all();
	}

	protected function getCertificateFilename() : string
	{
		return 'attestato-' . (Str::slug($this->getCourseAlias()) ?: 'corso') . '-' . $this->participation->getKey() . '.pdf';
	}
}
