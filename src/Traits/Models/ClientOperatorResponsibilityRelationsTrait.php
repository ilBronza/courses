<?php

namespace IlBronza\Courses\Traits\Models;

use IlBronza\Courses\Models\Responsibility;
use IlBronza\Operators\Models\ClientOperator;

trait ClientOperatorResponsibilityRelationsTrait
{
	public function clientOperator()
	{
		return $this->belongsTo(ClientOperator::gpc());
	}

	public function responsibility()
	{
		return $this->belongsTo(Responsibility::gpc());
	}
}
