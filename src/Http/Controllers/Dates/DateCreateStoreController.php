<?php

namespace IlBronza\Courses\Http\Controllers\Dates;

use IlBronza\CRUD\Traits\CRUDCreateStoreTrait;

class DateCreateStoreController extends DateCRUD
{
	use CRUDCreateStoreTrait;
	public $allowedMethods = ['create', 'store'];

	public function getAfterStoredRedirectUrl()
	{
		return $this->modelInstance->getShowUrl();
	}
}
