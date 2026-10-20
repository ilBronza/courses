<?php

namespace IlBronza\Courses\Contracts;

/** Implement on a participation model or on an adapter around another data source. */
interface CourseCertificateData
{
	public function getCourseAlias() : string;

	public function getHeaderData() : array;

	public function getParticipantData() : array;

	public function getCourseData() : array;

	public function getDisclaimerData() : array;

	public function getSignaturesData() : array;

	/** Absolute destination, including the PDF filename. Does not create the file. */
	public function getFilePath() : string;
}
