<?php

namespace IlBronza\Courses\Http\Controllers\Providers\FieldsGroups;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class OperatorResponsibilityFieldsGroupParametersFile extends FieldsGroupParametersFile
{
	static function getFieldsGroup() : array
	{
		return [
			'translationPrefix' => 'courses::fields',
			'fields' =>
			[
				'mySelfPrimary' => 'primary',
				'mySelfEdit' => 'links.edit',
				'mySelfSee' => 'links.see',
				'mySelfCalculate' => [
					'type' => 'links.ajaxUrl',
					'faIcon' => 'calendar-check',
					'function' => 'getCalculateUrl',
				],

				'operator.name' => 'flat',
				'responsibility.common_responsibility' => 'flat',
				'responsibility.description' => 'flat',

				'completed_at' => 'dates.date',
				'valid_to' => 'dates.date',
				'valid' => 'boolean',
				'errors' => 'flat',
				'parsed_at' => 'dates.datetime',
				'mySelfDelete' => 'links.delete',
			]
		];
	}
}
