<?php

namespace IlBronza\Courses\Http\Controllers\Providers\Fieldsets;

use IlBronza\Courses\Models\CourseTheme;
use IlBronza\Form\Helpers\FieldsetsProvider\FieldsetParametersFile;

class CourseSessionFieldsetsParameters extends FieldsetParametersFile
{
	public function _getFieldsetsParameters() : array
	{
		$model = $this->getModel();
		$courseId = request()->query('course_id');
		$themes = CourseTheme::gpc()::with('course')->whereHas('course')
			->when($courseId, fn ($query) => $query->where('course_id', $courseId))
			->orderBy('name')->get();
		$choices = $themes->mapWithKeys(fn ($theme) => [
			$theme->getKey() => "{$theme->course->name} — {$theme->name} ({$theme->program_status})",
		])->all();
		if($model?->exists && $theme = $model->courseTheme)
			$choices[$theme->getKey()] = $theme->name;

		return [
			'baseParameters' => [
				'translationPrefix' => 'courses::fields',
				'fields' => [
					'course_theme_id' => [
						'type' => 'select', 'select2' => false, 'multiple' => false,
						'possibleValuesArray' => $choices,
						'default' => $model?->course_theme_id ?? request()->query('course_theme_id'),
						'disabled' => (bool) $model?->exists,
						'rules' => 'required|string|exists:' . config('courses.models.courseTheme.table') . ',id,deleted_at,NULL',
					],
					'name' => ['text' => 'required|string|max:255'],
					'desired_start_date' => ['date' => 'nullable|date'],
					'desired_end_date' => ['date' => 'nullable|date'],
				],
				'width' => ['large'],
			],
		];
	}
}
