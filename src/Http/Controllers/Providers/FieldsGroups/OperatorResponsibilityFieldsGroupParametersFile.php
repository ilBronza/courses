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
				'mySelfCalculate' => 'courses::calculate',
				'operator.name' => 'flat',
				'responsibility.common_responsibility' => 'flat',
				'responsibility.description' => 'flat',
				'has_responsibility' => 'boolean',

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
