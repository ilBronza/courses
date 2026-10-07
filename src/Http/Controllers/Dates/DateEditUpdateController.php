<?php

namespace IlBronza\Courses\Http\Controllers\Dates;

use IlBronza\CRUD\Traits\CRUDEditUpdateTrait;
use Illuminate\Http\Request;

class DateEditUpdateController extends DateCRUD
{
	use CRUDEditUpdateTrait;
	public $allowedMethods = ['edit', 'update'];

	public function edit(string $date)
	{
		return $this->_edit($this->findModel($date));
	}

	public function update(Request $request, $date)
	{
		return $this->_update($request, $this->findModel($date));
	}
}
