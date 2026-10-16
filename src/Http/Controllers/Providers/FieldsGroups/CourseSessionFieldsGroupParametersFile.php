<?php

namespace IlBronza\Courses\Http\Controllers\Providers\FieldsGroups;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class CourseSessionFieldsGroupParametersFile extends FieldsGroupParametersFile
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
				'courseTheme.name' => 'flat',
				'name' => 'flat',
				'teacher_names' => 'flat',
				'desired_start_date' => 'dates.date',
				'desired_end_date' => 'dates.date',
				'mySelfDelete' => 'links.delete',
			],
		];
	}
}
