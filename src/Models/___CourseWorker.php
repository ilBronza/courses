<?php

namespace IlBronza\Courses\Models;

class CourseWorker extends CoursesPackageBasePivotModel
{
	protected $casts = ['completion_date' => 'date'];
	static $modelConfigPrefix = 'courseWorker';
	static $deletingRelationships = [];

	public function course()
	{
		return $this->belongsTo(Course::gpc());
	}

	public function worker()
	{
		return $this->belongsTo(Worker::gpc());
	}

	public function courseSession()
	{
		return $this->belongsTo(CourseSession::gpc());
	}
}
