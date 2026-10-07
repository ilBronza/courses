<?php

namespace IlBronza\Courses\Models;

use Illuminate\Validation\ValidationException;

/** An edition lesson: copied hours and type, with an independently planned date. */
class DateSession extends CoursesPackageBaseModel
{
	static $modelConfigPrefix = 'dateSession';
	static $deletingRelationships = [];
	protected $fillable = ['date_id', 'type_name', 'name', 'description', 'hours', 'sorting_index', 'starts_at'];
	protected $casts = ['hours' => 'decimal:2', 'sorting_index' => 'integer', 'starts_at' => 'datetime'];

	protected static function booted() : void
	{
		static::updating(function (self $lesson)
		{
			if($lesson->isDirty(['course_session_id', 'date_id', 'type_name', 'hours', 'sorting_index']))
				throw ValidationException::withMessages(['hours' => 'Le ore e la tipologia sono fissate dal programma copiato nell’edizione.']);
		});
	}

	public function courseSession()
	{
		return $this->belongsTo(CourseSession::gpc())->withTrashed();
	}

	public function date()
	{
		return $this->belongsTo(Date::gpc())->withTrashed();
	}
}
