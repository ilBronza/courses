<?php

namespace IlBronza\Courses\Http\Controllers\Providers\RelationshipsManagers;

use IlBronza\CRUD\Providers\RelationshipsManager\RelationshipsManager;

class CourseSessionRelationManager extends RelationshipsManager
{
	public function getAllRelationsParameters() : array
	{
		return ['show' => ['relations' => [
			'dateSessions' => [
				'sorting' => false,
				'controller' => config('courses.models.dateSession.controllers.index'),
				'fieldsGroupsParametersFile' => config('courses.models.dateSession.fieldsGroupsFiles.index'),
				'translatedTitle' => __('courses::courses.dateSessions'),
				'hasCreateButton' => false,
			],
		]]];
	}
}
