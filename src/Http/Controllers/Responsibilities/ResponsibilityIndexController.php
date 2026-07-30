<?php

namespace IlBronza\Courses\Http\Controllers\Responsibilities;

use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;

class ResponsibilityIndexController extends ResponsibilityCRUD
{
	use CRUDPlainIndexTrait;
	use CRUDIndexTrait;

	public $allowedMethods = ['index'];

	public function getIndexFieldsArray()
	{
		return config('courses.models.responsibility.fieldsGroupsFiles.index')::getTracedFieldsGroup();
	}

	public function getRelatedFieldsArray()
	{
		return $this->getIndexFieldsArray();
	}

	public function getIndexElements()
	{
		return $this->getModelClass()::withCount([
			'operatorResponsibilities',
			'clientOperatorResponsibilities'
		])->get();
	}
}
