<?php

namespace IlBronza\Courses\Http\Controllers\DateSessions;

use IlBronza\CRUD\Traits\CRUDShowTrait;

class DateSessionShowController extends DateSessionCRUD
{
	use CRUDShowTrait;
	public $allowedMethods = ['show'];

	public function getRelationshipsManagerClass()
	{
		return config('courses.models.dateSession.relationshipsManagerClasses.show');
	}

	public function show(string $dateSession)
	{
		return $this->_show($this->findModel($dateSession));
	}
}
