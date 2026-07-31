<?php

namespace IlBronza\Courses\Http\Controllers\OperatorResponsibilities;

use IlBronza\CRUD\Traits\CRUDEditUpdateTrait;
use Illuminate\Http\Request;

class OperatorResponsibilityEditUpdateController extends OperatorResponsibilityCRUD
{
	use CRUDEditUpdateTrait;

	public $allowedMethods = ['edit', 'update'];

	public function getGenericParametersFile() : ? string
	{
		return config('courses.models.operatorResponsibility.parametersFiles.edit');
	}

	public function edit(string $operatorResponsibility)
	{
		$operatorResponsibility = $this->findModel($operatorResponsibility);

		return $this->_edit($operatorResponsibility);
	}

	public function update(Request $request, $operatorResponsibility)
	{
		$operatorResponsibility = $this->findModel($operatorResponsibility);

		return $this->_update($request, $operatorResponsibility);
	}
}
