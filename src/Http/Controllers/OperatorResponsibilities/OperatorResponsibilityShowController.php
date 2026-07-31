<?php

namespace IlBronza\Courses\Http\Controllers\OperatorResponsibilities;

use IlBronza\CRUD\Traits\CRUDShowTrait;

class OperatorResponsibilityShowController extends OperatorResponsibilityCRUD
{
	use CRUDShowTrait;

	public $allowedMethods = ['show'];

	public function getGenericParametersFile() : ? string
	{
		return config('courses.models.operatorResponsibility.parametersFiles.show');
	}

	public function show(string $operatorResponsibility)
	{
		$operatorResponsibility = $this->findModel($operatorResponsibility);

		return $this->_show($operatorResponsibility);
	}
}
