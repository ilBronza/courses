<?php

namespace IlBronza\Courses\Http\Controllers\Dates;

use IlBronza\Courses\Http\Controllers\CRUDCoursesPackageController;

class DateCRUD extends CRUDCoursesPackageController
{
	public ?bool $updateEditor = false;
	public $saveAndCopy = false;
	public $createButtonIframe = false;
	public $configModelClassName = 'date';

	public function getGenericParametersFile() : ?string
	{
		return config('courses.models.date.parametersFiles.edit');
	}
}
