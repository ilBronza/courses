<?php

namespace IlBronza\Courses\Models;

use IlBronza\Courses\Traits\Models\ResponsibilityRelationsTrait;

class Responsibility extends CoursesPackageBaseModel
{
	use ResponsibilityRelationsTrait;

	static $modelConfigPrefix = 'responsibility';

	public function getCalculateUrl() : string
	{
		return $this->getKeyedRoute('calculateByResponsibility');
	}
}
