<?php

namespace IlBronza\Courses\Traits\Certificates;

use InvalidArgumentException;

/** Optional implementation of the path contract, reusable by future models. */
trait ResolvesCertificateFilePath
{
	abstract protected function getCertificateFilename() : string;

	public function getFilePath() : string
	{
		$filename = trim($this->getCertificateFilename());
		if($filename === '' || $filename === '.' || $filename === '..'
			|| preg_match('/[\\\\\/\x00-\x1F\x7F]/', $filename))
			throw new InvalidArgumentException('Il nome dell’attestato deve essere un nome file, senza percorsi o caratteri di controllo.');

		if(! preg_match('/\.pdf$/i', $filename))
			$filename .= '.pdf';

		if(strlen($filename) > 255)
			throw new InvalidArgumentException('Il nome dell’attestato supera i 255 byte.');

		$directory = config('courses.certificates.temp_directory') ?: storage_path('app/temp/course-certificates');
		if(! is_string($directory) || (! str_starts_with($directory, '/') && ! preg_match('/^[A-Za-z]:[\\\\\/]/', $directory)))
			throw new InvalidArgumentException('La cartella temporanea degli attestati deve essere un percorso assoluto.');

		return rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $filename;
	}
}
