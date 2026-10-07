<?php

namespace IlBronza\Courses\Http\Controllers\CourseSessions;

use IlBronza\CRUD\Traits\CRUDCreateStoreTrait;

class CourseSessionCreateStoreController extends CourseSessionCRUD
{
	use CRUDCreateStoreTrait;
	public $allowedMethods = ['create', 'store'];

	public function getAfterStoredRedirectUrl()
	{
		return $this->modelInstance->getShowUrl();
	}
}
