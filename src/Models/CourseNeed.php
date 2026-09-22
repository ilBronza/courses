<?php

namespace IlBronza\Courses\Models;

/**
 * Represents a generated training need for a course.
 *
 * This model has no table. Helpers configured in
 * courses.models.course.helpers.needs create its instances in memory.
 * Each instance must provide id, operator_name, client_name,
 * responsibility_name, expires_at, and note.
 */
class CourseNeed extends CoursesPackageBaseModel
{
	protected $guarded = [];

	static $modelConfigPrefix = 'courseNeed';

	protected $casts = [
		'completion' => 'date',
		'completion_ps_t1' => 'date',
		'completion_ps_t2' => 'date',
		'validity_date' => 'date',
		'expiration_date' => 'date',
		'responsibility_validation_date' => 'date',
		'expires_at' => 'date',
	];
}
