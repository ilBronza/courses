<?php

namespace IlBronza\Courses\Http\Controllers\Teachers;

use IlBronza\CRUD\Traits\CRUDShowTrait;

class TeacherShowController extends TeacherCRUD
{
	use CRUDShowTrait;
	public $allowedMethods = ['show'];

	public function getRelationshipsManagerClass()
	{
		return config('courses.models.teacher.relationshipsManagerClasses.show');
	}

	public function show(string $teacher)
	{
		return $this->_show($this->getModelClass()::withTrashed()->findOrFail($teacher));
	}
}
