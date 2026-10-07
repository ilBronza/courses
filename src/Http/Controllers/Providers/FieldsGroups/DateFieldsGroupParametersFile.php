<?php

namespace IlBronza\Courses\Http\Controllers\Providers\FieldsGroups;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class DateFieldsGroupParametersFile extends FieldsGroupParametersFile
{
	static function getFieldsGroup() : array
	{
		return [
			'translationPrefix' => 'courses::fields',
			'fields' => [
				'mySelfPrimary' => 'primary',
				'mySelfEdit' => 'links.edit',
				'mySelfSee' => 'links.see',
				'course.name' => 'flat',
				'name' => 'flat',
				'description' => 'flat',
				'hours' => 'flat',
				'sorting_index' => 'flat',
				'mySelfDelete' => 'links.delete',
			],
		];
	}
}
