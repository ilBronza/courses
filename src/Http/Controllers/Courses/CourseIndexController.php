<?php

namespace IlBronza\Courses\Http\Controllers\Courses;

use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;

class CourseIndexController extends CourseCRUD
{
	use CRUDPlainIndexTrait;
	use CRUDIndexTrait;

	public $allowedMethods = ['index'];

	public function getIndexFieldsArray()
	{
		return config('courses.models.course.fieldsGroupsFiles.index')::getTracedFieldsGroup();
	}

	public function getRelatedFieldsArray()
	{
		return $this->getIndexFieldsArray();
	}

	public function getIndexElements()
	{
		return $this->getModelClass()::orderBy('name')->get();
	}
}
