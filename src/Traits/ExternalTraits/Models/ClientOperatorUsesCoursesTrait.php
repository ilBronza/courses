<?php

namespace IlBronza\Courses\Traits\ExternalTraits\Models;

use IlBronza\Courses\Models\ClientOperatorResponsibility;
use IlBronza\Courses\Models\Responsibility;
use Illuminate\Database\Eloquent\Collection;

trait ClientOperatorUsesCoursesTrait
{
	public function clientOperatorResponsibilities()
	{
	    return $this->hasMany(
	        ClientOperatorResponsibility::gpc(),
	        'client_operator_id'
	    );
	}

	// public function responsibilities()
	// {
	// 	return $this->belongsToMany(
	// 		Responsibility::gpc(), config('courses.models.clientOperatorResponsibility.table')
	// 	)->using(ClientOperatorResponsibility::gpc())->withTimestamps();
	// }

	public function getResponsibilities() : Collection
	{
		return $this->responsibilities;
	}

	public function getCalculateUrl() : string
	{
		return route('ibCourses.clientOperators.calculateResponsibilities', [
			'clientOperator' => $this->getKey(),
		]);
	}
}
