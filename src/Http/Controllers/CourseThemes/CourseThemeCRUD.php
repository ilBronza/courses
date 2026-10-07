<?php

namespace IlBronza\Courses\Http\Controllers\CourseThemes;

use IlBronza\Courses\Http\Controllers\CRUDCoursesPackageController;
use Illuminate\Http\Request;

class CourseThemeCRUD extends CRUDCoursesPackageController
{
	public ?bool $updateEditor = false;

	public $configModelClassName = 'courseTheme';

	protected function prepareAteco(Request $request) : void
	{
		$request->validate([
			'ateco' => 'nullable|array',
			'ateco.*' => 'array:code',
			'program' => 'nullable|array',
			'program.*' => 'array:date_id,name,description,hours',
		]);

		// Removing every JSON row omits the field from the HTML form submission.
		$request->merge(['ateco' => $request->input('ateco') ?? []]);
		$request->merge(['program' => $request->input('program') ?? []]);
	}
}
