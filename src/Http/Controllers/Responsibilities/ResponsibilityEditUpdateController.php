<?php

namespace IlBronza\Courses\Http\Controllers\Responsibilities;

use IlBronza\CRUD\Traits\CRUDEditUpdateTrait;
use Illuminate\Http\Request;

class ResponsibilityEditUpdateController extends ResponsibilityCRUD
{
	use CRUDEditUpdateTrait;

	public $allowedMethods = ['edit', 'update'];

	public function getGenericParametersFile() : ? string
	{
		return config('courses.models.responsibility.parametersFiles.edit');
	}

	public function edit(string $responsibility)
	{
		$responsibility = $this->findModel($responsibility);

		return $this->_edit($responsibility);
	}

	public function update(Request $request, $responsibility)
	{
		$responsibility = $this->findModel($responsibility);

		return $this->_update($request, $responsibility);
	}
}
