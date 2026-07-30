<?php

namespace IlBronza\Courses\Http\Controllers\ClientOperatorResponsibilities;

use IlBronza\Courses\Http\Controllers\CRUDCoursesPackageController;

class ClientOperatorResponsibilityCRUD extends CRUDCoursesPackageController
{
	public ?bool $updateEditor = false;

	public $configModelClassName = 'clientOperatorResponsibility';
}
