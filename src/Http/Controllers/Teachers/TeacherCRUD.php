<?php

namespace IlBronza\Courses\Http\Controllers\Teachers;

use IlBronza\Courses\Http\Controllers\CRUDCoursesPackageController;

class TeacherCRUD extends CRUDCoursesPackageController
{
	public ?bool $updateEditor = false;
	public $saveAndCopy = false;
	public $createButtonIframe = false;
	public $configModelClassName = 'teacher';

	public function getGenericParametersFile() : ?string
	{
		return config('courses.models.teacher.parametersFiles.edit');
	}
}
