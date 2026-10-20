<?php

namespace IlBronza\Courses\Http\Controllers\Providers\FieldsGroups;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class OperatorCourseSessionFieldsGroupParametersFile extends FieldsGroupParametersFile
{
	static function getFieldsGroup() : array
	{
		return [
			'translationPrefix' => 'courses::fields',
			'fields' => [
				'mySelfPrimary' => 'primary',
				'operator.name' => 'flat',
				'courseSession.course.name' => 'flat',
				'courseSession.course.responsibility.description' => 'flat',
				'courseSession.name' => 'flat',
				'status' => 'flat',
				'completed_at' => 'dates.datetime',
				'result' => 'flat',
				'note' => 'flat',
				'created_at' => [
					'type' => 'dates.datetime',
					'order' => ['type' => 'desc'],
				],
			],
		];
	}
}
