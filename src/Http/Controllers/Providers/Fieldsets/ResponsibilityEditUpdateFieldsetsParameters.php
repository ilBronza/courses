<?php

namespace IlBronza\Courses\Http\Controllers\Providers\Fieldsets;

use IlBronza\Form\Helpers\FieldsetsProvider\FieldsetParametersFile;

class ResponsibilityEditUpdateFieldsetsParameters extends FieldsetParametersFile
{
	public function _getFieldsetsParameters() : array
	{
		return [
			'baseParameters' => [
				'translationPrefix' => 'courses::fields',
				'fields' => [
					'common_responsibility' => ['text' => 'string|nullable|max:16'],
					'description' => ['text' => 'string|nullable'],
				],
				'width' => ['large']
			],
		];
	}
}
