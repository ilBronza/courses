<?php

namespace IlBronza\Courses\Models;

use IlBronza\Courses\Models\CoursesPackageBasePivotModel;
use IlBronza\Courses\Models\Responsibility;
use IlBronza\Operators\Models\ClientOperator;

class ClientOperatorResponsibility extends CoursesPackageBasePivotModel
{
	static $modelConfigPrefix = 'clientOperatorResponsibility';
	static $deletingRelationships = [];

	public function clientOperator()
	{
		return $this->belongsTo(ClientOperator::gpc());
	}

	public function responsibility()
	{
		return $this->belongsTo(Responsibility::gpc());
	}
}
