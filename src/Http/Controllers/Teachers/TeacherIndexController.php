<?php

namespace IlBronza\Courses\Http\Controllers\Teachers;

use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;

class TeacherIndexController extends TeacherCRUD
{
	use CRUDPlainIndexTrait;
	use CRUDIndexTrait;
	public $allowedMethods = ['index'];

	public function getIndexFieldsArray()
	{
		return config('courses.models.teacher.fieldsGroupsFiles.index')::getTracedFieldsGroup();
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
