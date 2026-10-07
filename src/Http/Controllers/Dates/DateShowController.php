<?php

namespace IlBronza\Courses\Http\Controllers\Dates;

use IlBronza\CRUD\Traits\CRUDShowTrait;

class DateShowController extends DateCRUD
{
	use CRUDShowTrait;
	public $allowedMethods = ['show'];

	public function getRelationshipsManagerClass()
	{
		return config('courses.models.date.relationshipsManagerClasses.show');
	}

	public function show(string $date)
	{
		return $this->_show($this->findModel($date));
	}
}
