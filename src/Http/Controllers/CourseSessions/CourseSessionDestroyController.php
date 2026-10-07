<?php

namespace IlBronza\Courses\Http\Controllers\CourseSessions;

use IlBronza\CRUD\Traits\CRUDDeleteTrait;

class CourseSessionDestroyController extends CourseSessionCRUD
{
	use CRUDDeleteTrait;
	public $allowedMethods = ['destroy'];

	public function destroy($courseSession)
	{
		return $this->getModelClass()::resolveConnection()->transaction(
			fn () => $this->_destroy($this->findModel($courseSession))
		);
	}
}
