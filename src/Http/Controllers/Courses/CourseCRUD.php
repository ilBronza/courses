<?php

namespace IlBronza\Courses\Http\Controllers\Courses;

use IlBronza\Courses\Http\Controllers\CRUDCoursesPackageController;

class CourseCRUD extends CRUDCoursesPackageController
{
	public ?bool $updateEditor = false;

	public $configModelClassName = 'course';
}
