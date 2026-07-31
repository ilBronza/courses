<?php

namespace IlBronza\Courses\Http\Controllers\Providers\FieldsGroups;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class CourseFieldsGroupParametersFile extends FieldsGroupParametersFile
{
	static function getFieldsGroup() : array
	{
		return [
			'translationPrefix' => 'courses::fields',
			'fields' => [
				'mySelfPrimary' => 'primary',
				'mySelfEdit' => 'links.edit',
				'mySelfSee' => 'links.see',
				'name' => 'flat',
				'alias' => 'flat',
				'validity_months' => 'flat',
				'hours' => 'flat',
				'compulsory' => 'boolean',
				'e_learning' => 'boolean',
				'mySelfDelete' => 'links.delete',
			]
		];
	}
}
