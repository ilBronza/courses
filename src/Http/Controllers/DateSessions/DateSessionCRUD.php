<?php

namespace IlBronza\Courses\Http\Controllers\DateSessions;

use IlBronza\Courses\Http\Controllers\CRUDCoursesPackageController;

class DateSessionCRUD extends CRUDCoursesPackageController
{
	public ?bool $updateEditor = false;
	public $saveAndCopy = false;
	public $configModelClassName = 'dateSession';

	public function getGenericParametersFile() : ?string
	{
		return config('courses.models.dateSession.parametersFiles.edit');
	}
}
