<?php

namespace IlBronza\Courses\Http\Controllers\OperatorResponsibilities;

use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;

class OperatorResponsibilityIndexController extends OperatorResponsibilityCRUD
{
	use CRUDPlainIndexTrait;
	use CRUDIndexTrait;

	public $allowedMethods = ['index'];

	public function getIndexFieldsArray()
	{
		return config('courses.models.operatorResponsibility.fieldsGroupsFiles.index')::getTracedFieldsGroup();
	}

	public function getRelatedFieldsArray()
	{
		return config('courses.models.operatorResponsibility.fieldsGroupsFiles.relatedByOperator')::getTracedFieldsGroup();
	}

	public function getIndexElements()
	{
		return $this->getModelClass()::with([
			'operator',
			'responsibility'
		])->get();
	}
}
