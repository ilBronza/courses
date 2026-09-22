<?php

namespace IlBronza\Courses\Http\Controllers\CourseNeeds;

use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;
use Illuminate\Support\Collection;

/**
 * Displays the needs for the course identified by the route alias.
 */
class CourseNeedIndexController extends CourseNeedCRUD
{
	use CRUDPlainIndexTrait;
	use CRUDIndexTrait;

	public $allowedMethods = ['index'];

	public $avoidCreateButton = true;

	/**
	 * Return the fields to display for the generated course needs.
	 */
	public function getIndexFieldsArray()
	{
		return config('courses.models.courseNeed.fieldsGroupsFiles.index')::getTracedFieldsGroup();
	}

	/**
	 * Return the fields shared with related course-needs tables.
	 */
	public function getRelatedFieldsArray()
	{
		return $this->getIndexFieldsArray();
	}

	/**
	 * Retrieve the configured helper collection for the requested course alias.
	 */
	public function getIndexElements() : Collection
	{
		ini_set('max_execution_time', '120');
		ini_set('memory_limit', '-1');

		$helperClass = cconfig('courses.models.course.helpers.needs.' . request()->alias);

		return (new $helperClass(request()->alias))->getCollection();
	}
}
