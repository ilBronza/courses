<?php

namespace IlBronza\Courses\Contracts;

/** Optional capability; existing CourseCertificateData implementations remain valid. */
interface HasCertificateBackground
{
	/** Absolute local image path or PNG/JPEG/GIF data URI. */
	public function getCertificateBackground() : string;
}
