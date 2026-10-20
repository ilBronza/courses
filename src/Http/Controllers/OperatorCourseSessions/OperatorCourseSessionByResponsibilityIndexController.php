<?php

namespace IlBronza\Courses\Http\Controllers\OperatorCourseSessions;

use Illuminate\Database\Eloquent\Builder;

class OperatorCourseSessionByResponsibilityIndexController extends OperatorCourseSessionIndexController
{
	public function getIndexQuery() : Builder
	{
		return parent::getIndexQuery()->whereHas('courseSession.course',
			fn (Builder $query) => $query->byResponsibility(request()->route('responsibility'))
		);
	}
}
