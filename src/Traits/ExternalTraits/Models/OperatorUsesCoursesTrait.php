<?php

namespace IlBronza\Courses\Traits\ExternalTraits\Models;

use IlBronza\Courses\Models\OperatorResponsibility;
use IlBronza\Courses\Models\Responsibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait OperatorUsesCoursesTrait
{
	public function operatorResponsibilities()
	{
	    return $this->hasMany(
	        OperatorResponsibility::gpc(),
	        'operator_id'
	    );
	}

	public function specificOperatorResponsibility(Responsibility|string $responsibility) : HasOne
	{
		$responsibilityKey = $responsibility instanceof Responsibility ? $responsibility->getKey() : $responsibility;

		return $this->hasOne(
			OperatorResponsibility::gpc(),
			'operator_id'
		)->ofMany(
			['valid_to' => 'max'],
			fn (Builder $query) => $query->byResponsibility($responsibilityKey)
		);
	}

	public function getSpecificOperatorResponsibility(Responsibility|string $responsibility) : ? OperatorResponsibility
	{
		return $this->specificOperatorResponsibility($responsibility)->first();
	}

	public function getResponsibilities() : Collection
	{
		return $this->responsibilities;
	}

	public function getCalculateResponsibilityUrl() : string
	{
		return route('ibCourses.operators.calculateResponsibilities', [
			'operator' => $this->getKey(),
		]);
	}
}
