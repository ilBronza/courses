<?php

namespace IlBronza\Courses\Traits\Models;

trait OperatorResponsibilityGettersSettersTrait
{
	public function getResponsibilityName() : string
	{
		return $this->responsibility_id;
	}
}
