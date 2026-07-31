<?php

namespace IlBronza\Courses\Http\Controllers\Providers\FieldsGroups;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class ClientOperatorResponsibilityByResponsibilityFieldsGroupParametersFile extends FieldsGroupParametersFile
{
	static function getFieldsGroup() : array
	{
		return [
			'translationPrefix' => 'courses::fields',
			'fields' => [
				'mySelfPrimary' => 'primary',
				'mySelfCalculate' => [
					'type' => 'links.link',
					'function' => 'getCalculateUrl',
				],
				'clientOperator.operator.name' => 'flat',
				'clientOperator.client.name' => 'flat',
				'created_at' => 'dates.date',
			],
		];
	}
}
