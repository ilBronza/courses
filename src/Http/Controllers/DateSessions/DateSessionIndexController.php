<?php

namespace IlBronza\Courses\Http\Controllers\DateSessions;

use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;

class DateSessionIndexController extends DateSessionCRUD
{
	use CRUDPlainIndexTrait;
	use CRUDIndexTrait;
	public $allowedMethods = ['index'];
	public $avoidCreateButton = true;

	public function getIndexFieldsArray()
	{
		return config('courses.models.dateSession.fieldsGroupsFiles.index')::getTracedFieldsGroup();
	}

	public function getRelatedFieldsArray()
	{
		return $this->getIndexFieldsArray();
	}

	public function getIndexElements()
	{
		return $this->getModelClass()::with('courseSession')->orderBy('name')->get();
	}
}
