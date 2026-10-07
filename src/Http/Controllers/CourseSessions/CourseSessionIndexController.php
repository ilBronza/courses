<?php

namespace IlBronza\Courses\Http\Controllers\CourseSessions;

use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;

class CourseSessionIndexController extends CourseSessionCRUD
{
	use CRUDPlainIndexTrait;
	use CRUDIndexTrait;
	public $allowedMethods = ['index'];

	public function getIndexFieldsArray()
	{
		return config('courses.models.courseSession.fieldsGroupsFiles.index')::getTracedFieldsGroup();
	}

	public function getRelatedFieldsArray()
	{
		return $this->getIndexFieldsArray();
	}

	public function getIndexElements()
	{
		return $this->getModelClass()::with('courseTheme')->orderBy('name')->get();
	}
}
