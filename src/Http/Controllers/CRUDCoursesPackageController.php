<?php

namespace IlBronza\Courses\Http\Controllers;

use IlBronza\CRUD\Http\Controllers\BasePackageController;

class CRUDCoursesPackageController extends BasePackageController
{
	static $packageConfigPrefix = 'courses';

	public function getRouteBaseNamePrefix() : ? string
	{
		return config('courses.routePrefix');
	}

	public function setModelClass()
	{
		$this->modelClass = config("courses.models.{$this->configModelClassName}.class");
	}
}
