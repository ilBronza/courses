<?php

namespace IlBronza\Courses\Models;

class CourseSession extends CoursesPackageBaseUuidModel
{
	protected $casts = ['desired_start_date' => 'date', 'desired_end_date' => 'date', 'min_workers' => 'integer'];
	static $modelConfigPrefix = 'courseSession';
	static $deletingRelationships = ['courseWorkers', 'courseSessionDateSessions', 'workerCourseSessions'];

	public function course()
	{
		return $this->belongsTo(Course::gpc());
	}

	public function courseWorkers()
	{
		return $this->hasMany(CourseWorker::gpc());
	}

	public function courseSessionDateSessions()
	{
		return $this->hasMany(CourseSessionDateSession::gpc());
	}

	public function workerCourseSessions()
	{
		return $this->hasMany(WorkerCourseSession::gpc());
	}
}
