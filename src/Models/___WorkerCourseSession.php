<?php

namespace IlBronza\Courses\Models;

class WorkerCourseSession extends CoursesPackageBasePivotModel
{
	static $modelConfigPrefix = 'workerCoursesession';
	static $deletingRelationships = ['attendances'];

	public function courseSession()
	{
		return $this->belongsTo(CourseSession::gpc());
	}

	public function worker()
	{
		return $this->belongsTo(Worker::gpc());
	}

	public function attendances()
	{
		return $this->hasMany(Attendance::gpc(), 'coursesession_worker_id');
	}
}
