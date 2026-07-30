<?php

namespace IlBronza\Courses\Http\Controllers\ClientOperatorResponsibilities;

use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;

class ClientOperatorResponsibilityIndexController extends ClientOperatorResponsibilityCRUD
{
	use CRUDPlainIndexTrait;
	use CRUDIndexTrait;

	public $allowedMethods = ['index'];

	public function getIndexFieldsArray()
	{
		return config('courses.models.clientOperatorResponsibility.fieldsGroupsFiles.index')::getTracedFieldsGroup();
	}

	public function getRelatedFieldsArray()
	{
		return $this->getIndexFieldsArray();
	}

	public function getIndexElements()
	{
		return $this->getModelClass()::with([
			'clientOperator',
			'responsibility'
		])->get();
	}
}
