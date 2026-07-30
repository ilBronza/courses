<?php

namespace IlBronza\Courses\Models;

use IlBronza\Courses\Models\CoursesPackageBasePivotModel;
use IlBronza\Courses\Models\Responsibility;
use IlBronza\Operators\Models\Operator;

class OperatorResponsibility extends CoursesPackageBasePivotModel
{
	static $modelConfigPrefix = 'operatorResponsibility';
	static $deletingRelationships = [];

	protected $casts = [
		'completed_at' => 'datetime',
		'valid_to' => 'datetime',
		'parsed_at' => 'datetime',
		'valid' => 'boolean',
	];

	public function operator()
	{
		return $this->belongsTo(Operator::gpc());
	}

	public function responsibility()
	{
		return $this->belongsTo(Responsibility::gpc());
	}
}
