<?php

namespace IlBronza\Courses\Http\Controllers\Providers\FieldsGroups;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class CourseThemeFieldsGroupParametersFile extends FieldsGroupParametersFile
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
				'ateco_codes' => 'flat',
				'program_status' => 'flat',
				'mySelfDelete' => 'links.delete',
			],
		];
	}
}
