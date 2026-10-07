<?php

namespace IlBronza\Courses\Http\Controllers\CourseThemes;

use IlBronza\CRUD\Traits\CRUDEditUpdateTrait;
use Illuminate\Http\Request;

class CourseThemeEditUpdateController extends CourseThemeCRUD
{
	use CRUDEditUpdateTrait;

	public $allowedMethods = ['edit', 'update'];

	public function getGenericParametersFile() : ?string
	{
		return config('courses.models.courseTheme.parametersFiles.edit');
	}

	public function edit(string $courseTheme)
	{
		return $this->_edit($this->findModel($courseTheme));
	}

	public function update(Request $request, $courseTheme)
	{
		$courseTheme = $this->findModel($courseTheme);
		$this->prepareAteco($request);

		return $this->_update($request, $courseTheme);
	}
}
