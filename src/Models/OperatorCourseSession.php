<?php

namespace IlBronza\Courses\Models;

use IlBronza\Clients\Models\Client;
use IlBronza\Operators\Models\Operator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Validator;

/** An operator's participation in a course edition, including its outcome. */
class OperatorCourseSession extends CoursesPackageBaseModel
{
	static $modelConfigPrefix = 'operatorCourseSession';
	static $deletingRelationships = [];

	protected $fillable = ['operator_id', 'course_session_id', 'client_id', 'status', 'completed_at', 'result', 'note'];
	protected $casts = ['completed_at' => 'datetime'];

	protected static function booted() : void
	{
		static::saving(function (self $participation)
		{
			$rules = [
				'operator_id' => 'required|uuid',
				'course_session_id' => 'required|uuid',
				'client_id' => 'nullable|uuid',
				'status' => 'nullable|string|max:255',
				'completed_at' => 'nullable|date',
				'result' => 'nullable|string|max:255',
				'note' => 'nullable|string',
			];

			// Existing history remains editable after a parent is soft deleted.
			foreach([
				'operator_id' => config('operators.models.operator.table'),
				'course_session_id' => config('courses.models.courseSession.table'),
			] as $field => $table)
				if(! $participation->exists || $participation->isDirty($field))
					$rules[$field] .= '|exists:' . $table . ',id,deleted_at,NULL';

			Validator::make($participation->getAttributes(), $rules)->validate();
		});
	}

	public function operator() : BelongsTo
	{
		return $this->belongsTo(Operator::gpc(), 'operator_id')->withTrashed();
	}

	public function courseSession() : BelongsTo
	{
		return $this->belongsTo(CourseSession::gpc(), 'course_session_id')->withTrashed();
	}

	public function client() : BelongsTo
	{
		return $this->belongsTo(Client::gpc(), 'client_id')->withTrashed();
	}
}
