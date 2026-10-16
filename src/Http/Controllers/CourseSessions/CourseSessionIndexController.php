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
		return $this->getModelClass()::with(['course', 'courseTheme', 'teachers'])->orderBy('name')->get();
	}
}
