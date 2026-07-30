<?php

namespace IlBronza\Courses\Models;

use IlBronza\Courses\Models\CoursesPackageBaseModel;

class Course extends CoursesPackageBaseModel
{
	protected $casts = [
		'old_course_id' => 'integer',
		'validity_months' => 'integer',
		'hours' => 'integer',
		'max_missing_hours' => 'integer',
		'manager_id' => 'integer',
		'child_id' => 'integer',
		'need_parent' => 'boolean',
		'compulsory' => 'boolean',
		'parent_id' => 'integer',
		'makes_expiration_valid' => 'boolean',
		'price' => 'decimal:2',
		'user_area_order' => 'integer',
		'cumulative_hours' => 'boolean',
		'e_learning' => 'boolean',
	];

	static $modelConfigPrefix = 'course';
}
