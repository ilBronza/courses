<?php

namespace IlBronza\Courses\Http\Controllers\CourseThemes;

use IlBronza\CRUD\Traits\CRUDDeleteTrait;

class CourseThemeDestroyController extends CourseThemeCRUD
{
	use CRUDDeleteTrait;

	public $allowedMethods = ['destroy'];

	public function destroy($courseTheme)
	{
		return $this->_destroy($this->findModel($courseTheme));
	}
}
