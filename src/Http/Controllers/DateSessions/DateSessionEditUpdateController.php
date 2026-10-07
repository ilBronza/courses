<?php

namespace IlBronza\Courses\Http\Controllers\DateSessions;

use IlBronza\CRUD\Traits\CRUDEditUpdateTrait;
use Illuminate\Http\Request;

class DateSessionEditUpdateController extends DateSessionCRUD
{
	use CRUDEditUpdateTrait;
	public $allowedMethods = ['edit', 'update'];

	public function edit(string $dateSession)
	{
		return $this->_edit($this->findModel($dateSession));
	}

	public function update(Request $request, $dateSession)
	{
		return $this->_update($request, $this->findModel($dateSession));
	}
}
