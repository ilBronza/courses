<?php

namespace IlBronza\Courses\Http\Controllers\Courses;

use IlBronza\CRUD\Traits\CRUDShowTrait;

class CourseShowController extends CourseCRUD
{
	use CRUDShowTrait;

	public $allowedMethods = ['show'];

	public function getGenericParametersFile() : ? string
	{
		return config('courses.models.course.parametersFiles.show');
	}

	public function show(string $course)
	{
		$course = $this->findModel($course);

		return $this->_show($course);
	}
}
