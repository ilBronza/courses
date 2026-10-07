<?php

namespace IlBronza\Courses\Http\Controllers\CourseThemes;

use IlBronza\CRUD\Traits\CRUDShowTrait;

class CourseThemeShowController extends CourseThemeCRUD
{
	use CRUDShowTrait;

	public $allowedMethods = ['show'];

	public function getRelationshipsManagerClass()
	{
		return config('courses.models.courseTheme.relationshipsManagerClasses.show');
	}

	public function getGenericParametersFile() : ?string
	{
		return config('courses.models.courseTheme.parametersFiles.show');
	}

	public function show(string $courseTheme)
	{
		return $this->_show($this->findModel($courseTheme));
	}
}
