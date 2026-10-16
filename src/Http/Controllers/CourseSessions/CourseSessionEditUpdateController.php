<?php

namespace IlBronza\Courses\Http\Controllers\CourseSessions;

use IlBronza\CRUD\Traits\CRUDEditUpdateTrait;
use Illuminate\Http\Request;

class CourseSessionEditUpdateController extends CourseSessionCRUD
{
	use CRUDEditUpdateTrait;
	public $allowedMethods = ['edit', 'update'];

	public function edit(string $courseSession)
	{
		return $this->_edit($this->findModel($courseSession));
	}

	public function update(Request $request, $courseSession)
	{
		$edition = $this->findModel($courseSession);
		$this->prepareTeachers($request);

		return $edition->getConnection()->transaction(fn () => $this->_update($request, $edition));
	}
}
