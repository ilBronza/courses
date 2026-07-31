<?php

namespace IlBronza\Courses\Http\Controllers\Courses;

use IlBronza\CRUD\Traits\CRUDEditUpdateTrait;
use Illuminate\Http\Request;

class CourseEditUpdateController extends CourseCRUD
{
	use CRUDEditUpdateTrait;

	public $allowedMethods = ['edit', 'update'];

	public function getGenericParametersFile() : ? string
	{
		return config('courses.models.course.parametersFiles.edit');
	}

	public function edit(string $course)
	{
		$course = $this->findModel($course);

		return $this->_edit($course);
	}

	public function update(Request $request, $course)
	{
		$course = $this->findModel($course);

		return $this->_update($request, $course);
	}
}
