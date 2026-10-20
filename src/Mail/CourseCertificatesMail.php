<?php

namespace IlBronza\Courses\Mail;

use Illuminate\Mail\Mailable;

class CourseCertificatesMail extends Mailable
{
	public function __construct(public string $operatorName, public array $certificates)
	{
	}

	public function build()
	{
		$this->subject('Attestati di partecipazione ai corsi')->view('courses::mail.certificates');
		foreach($this->certificates as $certificate)
			$this->attachData($certificate['content'], $certificate['name'], ['mime' => 'application/pdf']);

		return $this;
	}
}
