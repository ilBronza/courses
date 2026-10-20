<?php

namespace IlBronza\Courses\Helpers\Certificates\Sources;

use IlBronza\Courses\Contracts\CourseCertificateData;
use IlBronza\Courses\Traits\Certificates\ExtractsLegacyCourseWorkerCertificateData;

/** Accepts both a DB query result (stdClass) and the Ecostudio Eloquent pivot. */
class LegacyCourseWorkerCertificateData implements CourseCertificateData
{
	use ExtractsLegacyCourseWorkerCertificateData;

	public function __construct(protected object $courseWorker)
	{
	}

	protected function getCourseWorker() : object
	{
		return $this->courseWorker;
	}
}
