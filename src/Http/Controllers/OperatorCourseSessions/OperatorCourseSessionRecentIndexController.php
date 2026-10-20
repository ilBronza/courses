<?php

namespace IlBronza\Courses\Http\Controllers\OperatorCourseSessions;

use Illuminate\Database\Eloquent\Builder;

class OperatorCourseSessionRecentIndexController extends OperatorCourseSessionIndexController
{
	// The rolling time window must also advance when no participation changes.
	public bool $useIndexCache = false;

	public function getIndexQuery() : Builder
	{
		$query = parent::getIndexQuery();

		return $query->where($query->getModel()->qualifyColumn('created_at'), '>=', now()->subMonthsNoOverflow(12));
	}
}
