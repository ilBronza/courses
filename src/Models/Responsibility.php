<?php

namespace IlBronza\Courses\Models;

use IlBronza\Courses\Traits\Models\ResponsibilityRelationsTrait;
use IlBronza\CRUD\Traits\Model\CRUDArchiverTrait;
use Illuminate\Database\Eloquent\Builder;

class Responsibility extends CoursesPackageBaseModel
{
	use CRUDArchiverTrait;
	use ResponsibilityRelationsTrait;

	static $modelConfigPrefix = 'responsibility';

	public function commonResponsibility()
	{
		return $this->belongsTo(static::class, 'common_responsibility');
	}

	public function childResponsibilities()
	{
		return $this->hasMany(static::class, 'common_responsibility');
	}

	public function courses()
	{
		return $this->hasMany(Course::gpc(), 'common_alias', 'id');
	}

	public function scopeByCommonResponsibility(Builder $query, string $responsibility): Builder
	{
		return $query->where('common_responsibility', $responsibility);
	}

	public function scopeRoot(Builder $query): Builder
	{
		return $query->whereNull('common_responsibility');
	}

	public function getCalculateResponsibilityUrl() : string
	{
		return $this->getKeyedRoute('calculateByResponsibility');
	}
}
