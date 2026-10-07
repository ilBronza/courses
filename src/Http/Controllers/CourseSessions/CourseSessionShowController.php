<?php

namespace IlBronza\Courses\Http\Controllers\CourseSessions;

use IlBronza\CRUD\Traits\CRUDShowTrait;

class CourseSessionShowController extends CourseSessionCRUD
{
	use CRUDShowTrait;
	public $allowedMethods = ['show'];

	public function getRelationshipsManagerClass()
	{
		return config('courses.models.courseSession.relationshipsManagerClasses.show');
	}

	public function show(string $courseSession)
	{
		return $this->_show($this->findModel($courseSession));
	}
}
