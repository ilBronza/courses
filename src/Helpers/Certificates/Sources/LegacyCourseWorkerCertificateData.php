<?php

namespace IlBronza\Courses\Helpers\Certificates\Sources;

use IlBronza\Courses\Contracts\CourseCertificateData;
use IlBronza\Courses\Contracts\HasCertificateBackground;
use IlBronza\Courses\Contracts\HasCertificateLogo;
use IlBronza\Courses\Helpers\Certificates\CertificateBackground;
use IlBronza\Courses\Helpers\Certificates\CertificateLogo;
use IlBronza\Courses\Traits\Certificates\ExtractsLegacyCourseWorkerCertificateData;

/** Accepts both a DB query result (stdClass) and the Ecostudio Eloquent pivot. */
class LegacyCourseWorkerCertificateData implements CourseCertificateData, HasCertificateBackground, HasCertificateLogo
{
	use ExtractsLegacyCourseWorkerCertificateData;

	public function __construct(protected object $courseWorker)
	{
	}

	public function getCertificateBackground() : string
	{
		return CertificateBackground::forCourseAlias($this->getCourseAlias());
	}

	public function getCertificateLogo() : ?string
	{
		return CertificateLogo::forCourseAlias($this->getCourseAlias());
	}

	protected function getCourseWorker() : object
	{
		return $this->courseWorker;
	}
}
