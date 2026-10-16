<?php

namespace IlBronza\Courses\Http\Controllers\CourseSessions;

use IlBronza\Courses\Http\Controllers\CRUDCoursesPackageController;
use Illuminate\Http\Request;

class CourseSessionCRUD extends CRUDCoursesPackageController
{
	public ?bool $updateEditor = false;
	public $saveAndCopy = false;
	public $createButtonIframe = false;
	public $configModelClassName = 'courseSession';

	protected function prepareTeachers(Request $request) : void
	{
		// An HTML multiple select omits the field when every option is removed.
		$request->merge(['teachers' => $request->input('teachers') ?? []]);
	}

	public function getGenericParametersFile() : ?string
	{
		return config('courses.models.courseSession.parametersFiles.edit');
	}
}
