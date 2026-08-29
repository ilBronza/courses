<?php

namespace IlBronza\Courses\Models;

use IlBronza\Courses\Models\CoursesPackageBaseModel;
use IlBronza\Courses\Models\Responsibility;
use Illuminate\Database\Eloquent\Builder;

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
		'required_valid_course_ids' => 'array',
		'expiration_target_course_ids' => 'array',
	];

	static $modelConfigPrefix = 'course';

	public function scopeByAlias(Builder $query, string $alias): Builder
	{
		return $query->where('alias', $alias);
	}

	public function scopeCompulsory(Builder $query): Builder
	{
		return $query->where('compulsory', true);
	}

	public function scopeOptional(Builder $query): Builder
	{
		return $query->where(function (Builder $query) {
			$query->where('compulsory', false)->orWhereNull('compulsory');
		});
	}

	public function scopeByResponsibility(Builder $query, string $responsibility): Builder
	{
		return $query->where('common_alias', $responsibility);
	}

	public function responsibility()
	{
		return $this->belongsTo(Responsibility::gpc(), 'common_alias');
	}

	public function getResponsibility() : Responsibility
	{
		return $this->responsibility;
	}
}
