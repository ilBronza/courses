<?php

namespace IlBronza\Courses\Http\Controllers\Providers\FieldsGroups;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class ClientOperatorResponsibilityFieldsGroupParametersFile extends FieldsGroupParametersFile
{
	static function getFieldsGroup() : array
	{
		return [
			'translationPrefix' => 'courses::fields',
			'fields' =>
			[
				'mySelfPrimary' => 'primary',

				'clientOperator.operator.name' => 'flat',
				'clientOperator.client.name' => 'flat',
				'responsibility.description' => 'flat',

				'created_at' => 'dates.date'
			]
		];
	}
}
