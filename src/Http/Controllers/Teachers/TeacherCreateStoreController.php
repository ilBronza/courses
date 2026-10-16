<?php

namespace IlBronza\Courses\Http\Controllers\Teachers;

use IlBronza\CRUD\Traits\CRUDCreateStoreTrait;

class TeacherCreateStoreController extends TeacherCRUD
{
	use CRUDCreateStoreTrait;
	public $allowedMethods = ['create', 'store'];
}
