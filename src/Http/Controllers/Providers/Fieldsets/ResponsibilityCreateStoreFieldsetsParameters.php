<?php

namespace IlBronza\Courses\Http\Controllers\Providers\Fieldsets;

class ResponsibilityCreateStoreFieldsetsParameters extends ResponsibilityEditUpdateFieldsetsParameters
{
	public function _getFieldsetsParameters() : array
	{
		$parameters = parent::_getFieldsetsParameters();

		//la sigla e' la chiave primaria: si assegna solo in creazione
		$parameters['baseParameters']['fields'] = [
			'id' => ['text' => 'string|required|max:16|unique:' . config('courses.models.responsibility.table') . ',id']
		] + $parameters['baseParameters']['fields'];

		return $parameters;
	}
}
