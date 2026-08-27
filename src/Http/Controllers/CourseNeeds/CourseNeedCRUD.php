<?php

namespace IlBronza\Courses\Http\Controllers\CourseNeeds;

use IlBronza\Courses\Http\Controllers\CRUDCoursesPackageController;

/**
 * Provides the shared CRUD configuration for generated course needs.
 */
class CourseNeedCRUD extends CRUDCoursesPackageController
{
	public $configModelClassName = 'courseNeed';
}
