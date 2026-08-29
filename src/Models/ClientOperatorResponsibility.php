<?php

namespace IlBronza\Courses\Models;

use IlBronza\Courses\Models\CoursesPackageBasePivotModel;
use IlBronza\Courses\Traits\Models\ClientOperatorResponsibilityGettersSettersTrait;
use IlBronza\Courses\Traits\Models\ClientOperatorResponsibilityRelationsTrait;
use IlBronza\Courses\Traits\Models\ClientOperatorResponsibilityScopesTrait;

class ClientOperatorResponsibility extends CoursesPackageBasePivotModel
{
	use ClientOperatorResponsibilityGettersSettersTrait;
	use ClientOperatorResponsibilityRelationsTrait;
	use ClientOperatorResponsibilityScopesTrait;

	static $modelConfigPrefix = 'clientOperatorResponsibility';
	static $deletingRelationships = [];

	public function getCalculateResponsibilityUrl() : string
	{
		return $this->getKeyedRoute('calculate');
	}
}
