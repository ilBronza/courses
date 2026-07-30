<?php

namespace IlBronza\Courses\Models;

class DateSession extends CoursesPackageBaseUuidModel
{
	protected $casts = ['hours' => 'decimal:2', 'starts_at' => 'datetime'];
	static $modelConfigPrefix = 'dateSession';
	static $deletingRelationships = ['courseSessionDateSessions'];

	public function date()
	{
		return $this->belongsTo(Date::gpc());
	}

	public function courseSessionDateSessions()
	{
		return $this->hasMany(CourseSessionDateSession::gpc());
	}
}
