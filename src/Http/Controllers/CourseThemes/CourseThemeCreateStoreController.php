<?php

namespace IlBronza\Courses\Http\Controllers\CourseThemes;

use IlBronza\CRUD\Traits\CRUDCreateStoreTrait;
use Illuminate\Http\Request;

class CourseThemeCreateStoreController extends CourseThemeCRUD
{
	use CRUDCreateStoreTrait;

	public $allowedMethods = ['create', 'store'];

	public function getGenericParametersFile() : ?string
	{
		return config('courses.models.courseTheme.parametersFiles.create');
	}

	public function store(Request $request, string $model = null)
	{
		$this->prepareAteco($request);

		return $this->_store($request);
	}
}
