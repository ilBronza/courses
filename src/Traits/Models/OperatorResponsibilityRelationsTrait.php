<?php

namespace IlBronza\Courses\Traits\Models;

use IlBronza\Courses\Models\Responsibility;
use IlBronza\Operators\Models\Operator;

trait OperatorResponsibilityRelationsTrait
{
	public function operator()
	{
		return $this->belongsTo(Operator::gpc());
	}

	public function getOperator() : ? Operator
	{
		return $this->operator;
	}

	public function responsibility()
	{
		return $this->belongsTo(Responsibility::gpc());
	}

	public function getResponsibility() : ? Responsibility
	{
		return $this->responsibility;
	}

}
