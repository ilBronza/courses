<?php

namespace IlBronza\Courses\Helpers\Certificates\Sources;

use IlBronza\Courses\Contracts\CourseCertificateData;
use IlBronza\Courses\Contracts\HasCertificateBackground;
use IlBronza\Courses\Helpers\Certificates\CertificateBackground;
use IlBronza\Courses\Traits\Certificates\ExtractsLegacyCourseWorkerCertificateData;

/** Accepts both a DB query result (stdClass) and the Ecostudio Eloquent pivot. */
class LegacyCourseWorkerCertificateData implements CourseCertificateData, HasCertificateBackground
{
	use ExtractsLegacyCourseWorkerCertificateData;

	public function __construct(protected object $courseWorker)
	{
	}

	public function getCertificateBackground() : string
	{
		return CertificateBackground::forCourseAlias($this->getCourseAlias());
	}

	protected function getCourseWorker() : object
	{
		return $this->courseWorker;
	}
}
