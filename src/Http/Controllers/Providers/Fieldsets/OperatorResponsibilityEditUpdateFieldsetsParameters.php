<?php

namespace IlBronza\Courses\Http\Controllers\Providers\Fieldsets;

class OperatorResponsibilityEditUpdateFieldsetsParameters extends OperatorResponsibilityCreateStoreFieldsetsParameters
{
	public function _getFieldsetsParameters() : array
	{
		$parameters = parent::_getFieldsetsParameters();

		$parameters['baseParameters']['fields']['operator_id']['readOnly'] = true;
		$parameters['baseParameters']['fields']['responsibility_id']['readOnly'] = true;

		return $parameters;
	}
}
