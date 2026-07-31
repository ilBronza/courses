<?php

namespace IlBronza\Courses\Http\Controllers\Courses;

use IlBronza\CRUD\Traits\CRUDCreateStoreTrait;

class CourseCreateStoreController extends CourseCRUD
{
	use CRUDCreateStoreTrait;

	public $allowedMethods = ['create', 'store'];

	public function getGenericParametersFile() : ? string
	{
		return config('courses.models.course.parametersFiles.create');
	}
}
