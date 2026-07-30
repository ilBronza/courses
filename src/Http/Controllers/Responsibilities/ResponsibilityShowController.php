<?php

namespace IlBronza\Courses\Http\Controllers\Responsibilities;

use IlBronza\CRUD\Traits\CRUDRelationshipTrait;
use IlBronza\CRUD\Traits\CRUDShowTrait;

class ResponsibilityShowController extends ResponsibilityCRUD
{
	use CRUDShowTrait;
	use CRUDRelationshipTrait;

	public $allowedMethods = ['show'];

	public function getGenericParametersFile() : ? string
	{
		return config('courses.models.responsibility.parametersFiles.show');
	}

	public function getRelationshipsManagerClass()
	{
		return config("courses.models.{$this->configModelClassName}.relationshipsManagerClasses.show");
	}

	public function show(string $responsibility)
	{
		$responsibility = $this->findModel($responsibility);

		return $this->_show($responsibility);
	}
}
