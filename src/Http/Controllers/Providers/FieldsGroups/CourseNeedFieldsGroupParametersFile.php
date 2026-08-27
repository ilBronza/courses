<?php

namespace IlBronza\Courses\Http\Controllers\Providers\FieldsGroups;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

/**
 * Defines the columns displayed by the generated course-needs index.
 */
class CourseNeedFieldsGroupParametersFile extends FieldsGroupParametersFile
{
	/**
	 * Return the course-needs index field-group definition.
	 *
	 * @return array<string, mixed>
	 */
	static function getFieldsGroup() : array
	{
		return [
			'translationPrefix' => 'courses::fields',
			'fields' => [
				'operator_name' => 'flat',
				'client_name' => 'flat',
				'responsibility_name' => 'flat',
				'expires_at' => 'dates.date',
				'note' => 'flat',
			]
		];
	}
}
