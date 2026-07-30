<?php

namespace IlBronza\Courses\Http\Controllers\Responsibilities;

use IlBronza\Courses\Http\Controllers\CRUDCoursesPackageController;

class ResponsibilityCRUD extends CRUDCoursesPackageController
{
	public ?bool $updateEditor = false;

	public $configModelClassName = 'responsibility';
}
