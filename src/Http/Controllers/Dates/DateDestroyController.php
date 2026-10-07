<?php

namespace IlBronza\Courses\Http\Controllers\Dates;

use IlBronza\CRUD\Traits\CRUDDeleteTrait;

class DateDestroyController extends DateCRUD
{
	use CRUDDeleteTrait;
	public $allowedMethods = ['destroy'];

	public function destroy($date)
	{
		return $this->getModelClass()::resolveConnection()->transaction(
			fn () => $this->_destroy($this->findModel($date))
		);
	}
}
