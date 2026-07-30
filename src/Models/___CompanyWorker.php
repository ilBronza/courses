<?php

namespace IlBronza\Courses\Models;

class CompanyWorker extends CoursesPackageBasePivotModel
{
	static $modelConfigPrefix = 'companyWorker';
	static $deletingRelationships = [];

	public function company()
	{
		return $this->belongsTo(Company::gpc());
	}

	public function worker()
	{
		return $this->belongsTo(Worker::gpc());
	}
}
