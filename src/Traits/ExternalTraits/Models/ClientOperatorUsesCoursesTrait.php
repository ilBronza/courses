<?php

namespace IlBronza\Courses\Traits\ExternalTraits\Models;

use IlBronza\Courses\Models\ClientOperatorResponsibility;
use IlBronza\Courses\Models\Responsibility;
use Illuminate\Database\Eloquent\Collection;

trait ClientOperatorUsesCoursesTrait
{
	public function clientOperatorResponsibilities()
	{
		return $this->hasMany(ClientOperatorResponsibility::gpc());
	}

	public function responsibilities()
	{
		return $this->belongsToMany(
			Responsibility::gpc(), config('courses.models.clientOperatorResponsibility.table')
		)->using(ClientOperatorResponsibility::gpc())->withTimestamps();
	}

	public function getResponsibilities() : Collection
	{
		return $this->responsibilities;
	}
}
