<?php

namespace IlBronza\Courses\Http\Controllers\Responsibilities;

use IlBronza\CRUD\Traits\CRUDDeleteTrait;

class ResponsibilityDestroyController extends ResponsibilityCRUD
{
	use CRUDDeleteTrait;

	public $allowedMethods = ['destroy'];

	public function destroy($responsibility)
	{
		$responsibility = $this->findModel($responsibility);

		return $this->_destroy($responsibility);
	}
}
