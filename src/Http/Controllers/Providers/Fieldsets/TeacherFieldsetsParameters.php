<?php

namespace IlBronza\Courses\Http\Controllers\Providers\Fieldsets;

use IlBronza\Form\Helpers\FieldsetsProvider\FieldsetParametersFile;

class TeacherFieldsetsParameters extends FieldsetParametersFile
{
	public function _getFieldsetsParameters() : array
	{
		return [
			'baseParameters' => [
				'translationPrefix' => 'courses::fields',
				'fields' => [
					'name' => ['text' => 'required|string|max:255'],
					'email' => ['email' => 'nullable|email|max:255'],
					'phone' => ['text' => 'nullable|string|max:50'],
					'note' => ['textarea' => 'nullable|string'],
				],
				'width' => ['large'],
			],
		];
	}
}
