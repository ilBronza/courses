<?php

namespace IlBronza\Courses\Models;

use IlBronza\Courses\Models\CoursesPackageBasePivotModel;
use IlBronza\Courses\Traits\Models\ClientOperatorResponsibilityGettersSettersTrait;
use IlBronza\Courses\Traits\Models\ClientOperatorResponsibilityRelationsTrait;

class ClientOperatorResponsibility extends CoursesPackageBasePivotModel
{
	use ClientOperatorResponsibilityGettersSettersTrait;
	use ClientOperatorResponsibilityRelationsTrait;

	static $modelConfigPrefix = 'clientOperatorResponsibility';
	static $deletingRelationships = [];

	public function getCalculateUrl() : string
	{
		return $this->getKeyedRoute('calculate');
	}
}
