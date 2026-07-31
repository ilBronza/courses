<?php

namespace IlBronza\Courses\Http\Controllers\OperatorResponsibilities;

class OperatorResponsibilityByResponsibilityIndexController extends OperatorResponsibilityIndexController
{
	public function getIndexElements()
	{
		return $this->getModelClass()::with([
			'operator',
			'responsibility',
		])
			->where('responsibility_id', request()->responsibility)
			->get();
	}
}
