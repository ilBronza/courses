<?php

namespace IlBronza\Courses\Http\Controllers\Dates;

use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;

class DateIndexController extends DateCRUD
{
	use CRUDPlainIndexTrait;
	use CRUDIndexTrait;
	public $allowedMethods = ['index'];

	public function getIndexFieldsArray()
	{
		return config('courses.models.date.fieldsGroupsFiles.index')::getTracedFieldsGroup();
	}

	public function getRelatedFieldsArray()
	{
		return $this->getIndexFieldsArray();
	}

	public function getIndexElements()
	{
		return $this->getModelClass()::with('course')->orderBy('name')->get();
	}
}
