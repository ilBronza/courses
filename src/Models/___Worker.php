<?php

namespace IlBronza\Courses\Models;

use IlBronza\Operators\Models\Operator;

class Worker extends CoursesPackageBaseUuidModel
{
	static $modelConfigPrefix = 'worker';
	static $deletingRelationships = ['companyWorkers', 'courseWorkers', 'workerCourseSessions'];

	public function operator()
	{
		return $this->belongsTo(Operator::gpc(), 'operator_id');
	}

	public function companyWorkers()
	{
		return $this->hasMany(CompanyWorker::gpc());
	}

	public function courseWorkers()
	{
		return $this->hasMany(CourseWorker::gpc());
	}

	public function workerCourseSessions()
	{
		return $this->hasMany(WorkerCourseSession::gpc());
	}
}
