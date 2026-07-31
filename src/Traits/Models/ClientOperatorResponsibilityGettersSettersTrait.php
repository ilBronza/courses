<?php

namespace IlBronza\Courses\Traits\Models;

trait ClientOperatorResponsibilityGettersSettersTrait
{
	public function getResponsibilityName() : string
	{
		return $this->responsibility_id;
	}
}
