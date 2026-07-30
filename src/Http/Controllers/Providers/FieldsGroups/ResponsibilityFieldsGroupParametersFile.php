<?php

namespace IlBronza\Courses\Http\Controllers\Providers\FieldsGroups;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class ResponsibilityFieldsGroupParametersFile extends FieldsGroupParametersFile
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
				'id' => 'flat',
				'description' => 'flat',
				'operator_responsibilities_count' => 'flat',
				'client_operator_responsibilities_count' => 'flat',
				'mySelfDelete' => 'links.delete'
			]
		];
	}
}
