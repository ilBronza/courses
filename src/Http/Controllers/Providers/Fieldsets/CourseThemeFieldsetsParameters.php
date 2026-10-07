<?php

namespace IlBronza\Courses\Http\Controllers\Providers\Fieldsets;

use IlBronza\Courses\Helpers\CourseProgram;
use IlBronza\Courses\Models\Date;
use IlBronza\Form\Helpers\FieldsetsProvider\FieldsetParametersFile;

class CourseThemeFieldsetsParameters extends FieldsetParametersFile
{
	public function _getFieldsetsParameters() : array
	{
		$courseId = $this->getModel()?->course_id ?? request()->input('course_id');
		$types = Date::gpc()::with('course')->whereHas('course')
			->when($courseId, fn ($query) => $query->where('course_id', $courseId))
			->orderBy('sorting_index')->orderBy('name')->get();
		$typeOptions = $types->mapWithKeys(fn ($type) => [
			$type->getKey() => "{$type->course->name} — {$type->name} ({$type->hours} ore)",
		])->all();
		$program = $this->getModel()?->program ?? [];
		if(! $program && $courseId && request()->routeIs('*.create', '*.edit'))
			$program = $types->map(fn ($type) => [
				'date_id' => $type->getKey(), 'name' => $type->name,
				'hours' => $type->hours, 'description' => $type->description,
			])->all();

		return [
			'baseParameters' => [
				'translationPrefix' => 'courses::fields',
				'fields' => [
					'course_id' => [
						'type' => 'select',
						'select2' => false,
						'multiple' => false,
						'relation' => 'course',
						'default' => $this->getModel()?->course_id ?? request()->query('course_id'),
						'rules' => 'required|string|exists:' . config('courses.models.course.table') . ',id,deleted_at,NULL',
					],
					'name' => ['text' => 'required|string|max:255'],
					'description' => ['textarea' => 'nullable|string'],
				],
				'width' => ['large'],
			],
			'atecoParameters' => [
				'translationPrefix' => 'courses::fields',
				'fields' => [
					'ateco' => [
						'type' => 'json',
						'fields' => [
							'code' => [
								'type' => 'text',
								'label' => __('courses::fields.ateco_code'),
								'rules' => 'nullable|string|max:32|distinct',
							],
						],
						'rules' => 'nullable|array',
					],
				],
				'width' => ['large'],
			],
			'programParameters' => [
				'translationPrefix' => 'courses::fields',
				'fields' => [
					'program_requirements' => [
						'type' => 'text', 'readOnly' => true, 'rules' => 'nullable',
						'value' => $courseId && $types->isNotEmpty()
							? $types->map(fn ($type) => "{$type->name}: {$type->hours} ore")->implode('; ')
							: 'Seleziona il corso e definisci le sue tipologie di data.',
					],
					'program_status' => ['type' => 'text', 'readOnly' => true, 'rules' => 'nullable'],
					'program' => [
						'type' => 'json',
						'value' => $program,
						'fields' => [
							'date_id' => [
								'type' => 'select', 'select2' => false, 'multiple' => false,
								'possibleValuesArray' => $typeOptions, 'rules' => 'required|string',
							],
							'name' => ['text' => 'required|string|max:255'],
							'hours' => ['type' => 'number', 'step' => '0.01', 'rules' => CourseProgram::HOURS_RULES],
							'description' => ['textarea' => 'nullable|string'],
						],
						'rules' => 'nullable|array',
					],
				],
				'width' => ['large'],
			],
		];
	}
}
