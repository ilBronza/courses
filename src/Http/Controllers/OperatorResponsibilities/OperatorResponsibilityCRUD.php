<?php

namespace IlBronza\Courses\Http\Controllers\OperatorResponsibilities;

use IlBronza\Courses\Http\Controllers\CRUDCoursesPackageController;

class OperatorResponsibilityCRUD extends CRUDCoursesPackageController
{
	public ?bool $updateEditor = false;

	public $configModelClassName = 'operatorResponsibility';
}
