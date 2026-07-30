<?php

namespace IlBronza\Courses\Models;

class Date extends CoursesPackageBaseUuidModel
{
	protected $casts = ['hours' => 'decimal:2', 'minimum_hours' => 'decimal:2', 'remoteable' => 'boolean', 'sorting_index' => 'integer'];
	static $modelConfigPrefix = 'date';
	static $deletingRelationships = ['dateSessions'];

	public function course()
	{
		return $this->belongsTo(Course::gpc());
	}

	public function dateSessions()
	{
		return $this->hasMany(DateSession::gpc());
	}
}
