<?php

namespace IlBronza\Courses\Http\Controllers\OperatorResponsibilities;

use IlBronza\CRUD\Traits\CRUDCreateStoreTrait;

class OperatorResponsibilityCreateStoreController extends OperatorResponsibilityCRUD
{
	use CRUDCreateStoreTrait;

	public $allowedMethods = ['create', 'store'];

	public function getGenericParametersFile() : ? string
	{
		return config('courses.models.operatorResponsibility.parametersFiles.create');
	}
}
