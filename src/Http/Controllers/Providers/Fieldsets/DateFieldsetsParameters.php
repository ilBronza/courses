<?php

namespace IlBronza\Courses\Http\Controllers\Providers\Fieldsets;

use IlBronza\Courses\Helpers\CourseProgram;
use IlBronza\Form\Helpers\FieldsetsProvider\FieldsetParametersFile;

class DateFieldsetsParameters extends FieldsetParametersFile
{
	public function _getFieldsetsParameters() : array
	{
		return [
			'baseParameters' => [
				'translationPrefix' => 'courses::fields',
				'fields' => [
					'course_id' => [
						'type' => 'select', 'select2' => false, 'multiple' => false, 'relation' => 'course',
						'default' => $this->getModel()?->course_id ?? request()->query('course_id'),
						'rules' => 'required|string|exists:' . config('courses.models.course.table') . ',id,deleted_at,NULL',
					],
					'name' => ['text' => 'required|string|max:255'],
					'description' => ['textarea' => 'nullable|string'],
					'hours' => ['type' => 'number', 'step' => '0.01', 'rules' => CourseProgram::HOURS_RULES],
					'sorting_index' => ['type' => 'number', 'default' => 0, 'rules' => 'required|integer|min:0|max:10000'],
				],
				'width' => ['large'],
			],
		];
	}
}
