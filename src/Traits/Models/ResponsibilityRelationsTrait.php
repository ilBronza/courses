<?php

namespace IlBronza\Courses\Traits\Models;

use IlBronza\Courses\Models\ClientOperatorResponsibility;
use IlBronza\Courses\Models\OperatorResponsibility;

trait ResponsibilityRelationsTrait
{
	public function clientOperatorResponsibilities()
	{
		return $this->hasMany(ClientOperatorResponsibility::gpc());
	}

	public function operatorResponsibilities()
	{
		return $this->hasMany(OperatorResponsibility::gpc());
	}
}
