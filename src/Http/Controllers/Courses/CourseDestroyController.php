<?php

namespace IlBronza\Courses\Http\Controllers\Courses;

use IlBronza\CRUD\Traits\CRUDDeleteTrait;

class CourseDestroyController extends CourseCRUD
{
	use CRUDDeleteTrait;

	public $allowedMethods = ['destroy'];

	public function destroy($course)
	{
		$course = $this->findModel($course);

		return $this->_destroy($course);
	}
}
