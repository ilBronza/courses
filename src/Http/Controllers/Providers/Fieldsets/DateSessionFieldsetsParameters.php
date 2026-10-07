<?php

namespace IlBronza\Courses\Http\Controllers\Providers\Fieldsets;

use IlBronza\Form\Helpers\FieldsetsProvider\FieldsetParametersFile;

class DateSessionFieldsetsParameters extends FieldsetParametersFile
{
	public function _getFieldsetsParameters() : array
	{
		return [
			'baseParameters' => [
				'translationPrefix' => 'courses::fields',
				'fields' => [
					'type_name' => ['type' => 'text', 'readOnly' => true, 'rules' => 'nullable'],
					'hours' => ['type' => 'number', 'readOnly' => true, 'rules' => 'nullable'],
					'name' => ['text' => 'required|string|max:255'],
					'description' => ['textarea' => 'nullable|string'],
					'starts_at' => ['datetime' => 'nullable|date'],
				],
				'width' => ['large'],
			],
		];
	}
}
