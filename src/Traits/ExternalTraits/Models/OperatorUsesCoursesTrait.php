<?php

namespace IlBronza\Courses\Traits\ExternalTraits\Models;

use IlBronza\Courses\Models\OperatorResponsibility;
use Illuminate\Database\Eloquent\Collection;

trait OperatorUsesCoursesTrait
{
	public function operatorResponsibilities()
	{
	    return $this->hasMany(
	        OperatorResponsibility::gpc(),
	        'operator_id'
	    );
	}

	public function getResponsibilities() : Collection
	{
		return $this->responsibilities;
	}

	public function getCalculateUrl() : string
	{
		return route('ibCourses.operators.calculateResponsibilities', [
			'operator' => $this->getKey(),
		]);
	}
}
