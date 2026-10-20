<?php

namespace IlBronza\Courses\Http\Controllers\OperatorCourseSessions;

use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;
use Illuminate\Database\Eloquent\Builder;

class OperatorCourseSessionIndexController extends OperatorCourseSessionCRUD
{
	use CRUDPlainIndexTrait;
	use CRUDIndexTrait;

	public $allowedMethods = ['index'];
	public $avoidCreateButton = true;

	public function getIndexFieldsArray()
	{
		return config('courses.models.operatorCourseSession.fieldsGroupsFiles.index')::getTracedFieldsGroup();
	}

	public function getRelatedFieldsArray()
	{
		return $this->getIndexFieldsArray();
	}

	public function getIndexQuery() : Builder
	{
		return $this->getModelClass()::with([
			'operator',
			'courseSession.course.responsibility',
		])->orderByDesc('created_at')->orderByDesc('id');
	}

	public function getIndexElements()
	{
		return $this->getIndexQuery()->get();
	}
}
