<?php

namespace IlBronza\Courses\Models;

class CourseSessionDateSession extends CoursesPackageBasePivotModel
{
	static $modelConfigPrefix = 'coursesessionDatesession';
	static $deletingRelationships = ['attendances'];

	public function courseSession()
	{
		return $this->belongsTo(CourseSession::gpc());
	}

	public function dateSession()
	{
		return $this->belongsTo(DateSession::gpc());
	}

	public function attendances()
	{
		return $this->hasMany(Attendance::gpc(), 'coursesession_datesession_id');
	}
}
