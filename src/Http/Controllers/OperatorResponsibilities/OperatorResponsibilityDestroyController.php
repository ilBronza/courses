<?php

namespace IlBronza\Courses\Http\Controllers\OperatorResponsibilities;

use IlBronza\CRUD\Traits\CRUDDeleteTrait;

class OperatorResponsibilityDestroyController extends OperatorResponsibilityCRUD
{
	use CRUDDeleteTrait;

	public $allowedMethods = ['destroy'];

	public function destroy($operatorResponsibility)
	{
		$operatorResponsibility = $this->findModel($operatorResponsibility);

		return $this->_destroy($operatorResponsibility);
	}
}
