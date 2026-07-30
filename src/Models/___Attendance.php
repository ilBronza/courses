<?php

namespace IlBronza\Courses\Models;

class Attendance extends CoursesPackageBasePivotModel
{
	protected $casts = ['hours' => 'decimal:2'];
	static $modelConfigPrefix = 'attendance';
	static $deletingRelationships = [];

	public function courseSessionDateSession()
	{
		return $this->belongsTo(CourseSessionDateSession::gpc(), 'coursesession_datesession_id');
	}

	public function workerCourseSession()
	{
		return $this->belongsTo(WorkerCourseSession::gpc(), 'coursesession_worker_id');
	}
}
