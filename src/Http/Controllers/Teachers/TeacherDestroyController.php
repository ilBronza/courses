<?php

namespace IlBronza\Courses\Http\Controllers\Teachers;

use IlBronza\CRUD\Traits\CRUDDeleteTrait;

class TeacherDestroyController extends TeacherCRUD
{
	use CRUDDeleteTrait;
	public $allowedMethods = ['destroy'];

	public function destroy($teacher)
	{
		return $this->_destroy($this->findModel($teacher));
	}
}
