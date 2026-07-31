<?php

namespace IlBronza\Courses\Http\Controllers\Providers\Fieldsets;

use IlBronza\Form\Helpers\FieldsetsProvider\FieldsetParametersFile;

class OperatorResponsibilityCreateStoreFieldsetsParameters extends FieldsetParametersFile
{
	public function _getFieldsetsParameters() : array
	{
		return [
			'baseParameters' => [
				'translationPrefix' => 'courses::fields',
				'fields' => [
					'operator_id' => [
						'type' => 'select',
						'select2' => false,
						'multiple' => false,
						'rules' => 'string|required|exists:' . config('operators.models.operator.table') . ',id',
						'relation' => 'operator',
					],
					'responsibility_id' => [
						'type' => 'select',
						'select2' => false,
						'multiple' => false,
						'rules' => 'string|required|exists:' . config('courses.models.responsibility.table') . ',id',
						'relation' => 'responsibility',
					],
					'completed_at' => ['date' => 'date|nullable'],
				],
				'width' => ['large'],
			],
		];
	}
}
