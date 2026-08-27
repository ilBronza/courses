<?php

namespace IlBronza\Courses\Http\Controllers\Providers\FieldsGroups;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class OperatorResponsibilityByOperatorFieldsGroupParametersFile extends FieldsGroupParametersFile
{
	static function getFieldsGroup() : array
	{
		return [
			'translationPrefix' => 'courses::fields',
			'fields' => [
				'mySelfPrimary' => 'primary',
				'mySelfCalculate' => [
					'type' => 'links.link',
					'function' => 'getCalculateResponsibilityUrl',
				],
				'responsibility.common_responsibility' => 'flat',
				'responsibility.description' => 'flat',
				'assumed' => 'boolean',
				'completed_at' => 'dates.date',
				'valid_to' => 'dates.date',
				'valid' => 'boolean',
				'errors' => 'flat',
				'parsed_at' => 'dates.datetime',
			],
		];
	}
}
