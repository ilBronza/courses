<?php

namespace IlBronza\Courses\Http\Controllers\Providers\Fieldsets;

use IlBronza\Form\Helpers\FieldsetsProvider\FieldsetParametersFile;

class CourseEditUpdateFieldsetsParameters extends FieldsetParametersFile
{
	public function _getFieldsetsParameters() : array
	{
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
		];
	}
}
