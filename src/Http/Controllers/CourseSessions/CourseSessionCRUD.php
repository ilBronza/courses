<?php

namespace IlBronza\Courses\Http\Controllers\CourseSessions;

use IlBronza\Courses\Http\Controllers\CRUDCoursesPackageController;

class CourseSessionCRUD extends CRUDCoursesPackageController
{
	public ?bool $updateEditor = false;
	public $saveAndCopy = false;
	public $createButtonIframe = false;
	public $configModelClassName = 'courseSession';

	public function getGenericParametersFile() : ?string
	{
		return config('courses.models.courseSession.parametersFiles.edit');
	}
}
