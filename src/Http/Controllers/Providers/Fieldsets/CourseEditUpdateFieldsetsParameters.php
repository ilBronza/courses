<?php

namespace IlBronza\Courses\Http\Controllers\Providers\Fieldsets;

use IlBronza\Courses\Models\Course;
use IlBronza\Form\Helpers\FieldsetsProvider\FieldsetParametersFile;

class CourseEditUpdateFieldsetsParameters extends FieldsetParametersFile
{
	public function getSelectableCoursesArray() : array
	{
		$courseClass = Course::gpc();
		$query = $courseClass::query()->orderBy('name');

		if($courseId = $this->getModel()?->getKey())
			$query->where($query->getModel()->getKeyName(), '!=', $courseId);

		return $query->pluck('name', 'id')->all();
	}

	public function _getFieldsetsParameters() : array
	{
		$selectableCourses = $this->getSelectableCoursesArray();
		$courseTable = config('courses.models.course.table');

		return [
			'baseParameters' => [
				'translationPrefix' => 'courses::fields',
				'fields' => [
					'name' => ['text' => 'string|required|max:255'],
					'alias' => ['text' => 'string|nullable|max:255'],
					'common_alias' => ['text' => 'string|nullable|max:32'],
					'description' => ['textarea' => 'string|nullable'],
					'description_subscribed' => ['textarea' => 'string|nullable'],
					'note' => ['textarea' => 'string|nullable'],
				],
				'width' => ['large'],
			],
			'durationParameters' => [
				'translationPrefix' => 'courses::fields',
				'fields' => [
					'validity_months' => ['number' => 'integer|nullable|min:0'],
					'hours' => ['number' => 'integer|nullable|min:0'],
					'max_missing_hours' => ['number' => 'integer|nullable|min:0'],
					'price' => ['number' => 'numeric|nullable|min:0'],
				],
				'width' => ['large'],
			],
			'optionsParameters' => [
				'translationPrefix' => 'courses::fields',
				'fields' => [
					'need_parent' => ['boolean' => 'boolean|nullable'],
					'compulsory' => ['boolean' => 'boolean|nullable'],
					'makes_expiration_valid' => ['boolean' => 'boolean|nullable'],
					'cumulative_hours' => ['boolean' => 'boolean|nullable'],
					'e_learning' => ['boolean' => 'boolean|nullable'],
				],
				'width' => ['large'],
			],
			'validityRelationsParameters' => [
				'translationPrefix' => 'courses::fields',
				'fields' => [
					'required_valid_course_ids' => [
						'type' => 'json',
						'fields' => [
							'course_id' => [
								'type' => 'select',
								'select2' => false,
								'multiple' => false,
								'possibleValuesArray' => $selectableCourses,
								'rules' => 'string|required|exists:' . $courseTable . ',id',
							],
						],
						'rules' => 'array|nullable',
					],
					'expiration_target_course_ids' => [
						'type' => 'json',
						'fields' => [
							'course_id' => [
								'type' => 'select',
								'multiple' => false,
								'select2' => false,
								'possibleValuesArray' => $selectableCourses,
								'rules' => 'string|required|exists:' . $courseTable . ',id',
							],
						],
						'rules' => 'array|nullable',
					],
				],
				'width' => ['large'],
			],
		];
	}
}
