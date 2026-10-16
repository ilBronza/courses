<?php

namespace IlBronza\Courses\Http\Controllers\Teachers;

use IlBronza\CRUD\Traits\CRUDEditUpdateTrait;
use Illuminate\Http\Request;

class TeacherEditUpdateController extends TeacherCRUD
{
	use CRUDEditUpdateTrait;
	public $allowedMethods = ['edit', 'update'];

	public function edit(string $teacher)
	{
		return $this->_edit($this->findModel($teacher));
	}

	public function update(Request $request, $teacher)
	{
		return $this->_update($request, $this->findModel($teacher));
	}
}
