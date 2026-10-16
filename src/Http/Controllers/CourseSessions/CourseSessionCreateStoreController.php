<?php

namespace IlBronza\Courses\Http\Controllers\CourseSessions;

use IlBronza\CRUD\Traits\CRUDCreateStoreTrait;
use Illuminate\Http\Request;

class CourseSessionCreateStoreController extends CourseSessionCRUD
{
	use CRUDCreateStoreTrait;
	public $allowedMethods = ['create', 'store'];

	public function store(Request $request, string $model = null)
	{
		$this->prepareTeachers($request);

		return $this->getModelClass()::resolveConnection()->transaction(fn () => $this->_store($request));
	}

	public function getAfterStoredRedirectUrl()
	{
		return $this->modelInstance->getShowUrl();
	}
}
