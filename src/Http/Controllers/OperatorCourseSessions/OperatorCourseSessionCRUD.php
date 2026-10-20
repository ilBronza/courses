<?php

namespace IlBronza\Courses\Http\Controllers\OperatorCourseSessions;

use IlBronza\Courses\Http\Controllers\CRUDCoursesPackageController;

class OperatorCourseSessionCRUD extends CRUDCoursesPackageController
{
	public ?bool $updateEditor = false;
	public $configModelClassName = 'operatorCourseSession';
}
