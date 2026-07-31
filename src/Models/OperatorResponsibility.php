<?php

namespace IlBronza\Courses\Models;

use IlBronza\Courses\Models\CoursesPackageBasePivotModel;
use IlBronza\Courses\Traits\Models\OperatorResponsibilityGettersSettersTrait;
use IlBronza\Courses\Traits\Models\OperatorResponsibilityRelationsTrait;
use IlBronza\Courses\Traits\Models\OperatorResponsibilityScopesTrait;

class OperatorResponsibility extends CoursesPackageBasePivotModel
{
	use OperatorResponsibilityGettersSettersTrait;
	use OperatorResponsibilityRelationsTrait;
	use OperatorResponsibilityScopesTrait;

	static $modelConfigPrefix = 'operatorResponsibility';
	static $deletingRelationships = [];

	protected $casts = [
		'completed_at' => 'datetime',
		'valid_to' => 'datetime',
		'parsed_at' => 'datetime',
		'valid' => 'boolean',
	];

	public function getCalculateUrl() : string
	{
		return $this->getKeyedRoute('calculateByOperatorResponsibility');
	}
}
