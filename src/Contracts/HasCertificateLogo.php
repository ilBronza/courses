<?php

namespace IlBronza\Courses\Contracts;

/** Optional capability for the main logo, independently of partner organizations. */
interface HasCertificateLogo
{
	/** Absolute local image path or PNG/JPEG/GIF data URI; null if none is available. */
	public function getCertificateLogo() : ?string;
}
