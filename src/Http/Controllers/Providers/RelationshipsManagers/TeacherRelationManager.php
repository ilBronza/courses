<?php

namespace IlBronza\Courses\Http\Controllers\Providers\RelationshipsManagers;

use IlBronza\CRUD\Providers\RelationshipsManager\RelationshipsManager;

class TeacherRelationManager extends RelationshipsManager
{
	public function getAllRelationsParameters() : array
	{
		return ['show' => ['relations' => [
			'courseSessions' => [
				'sorting' => false,
				'controller' => config('courses.models.courseSession.controllers.index'),
				'fieldsGroupsParametersFile' => config('courses.models.courseSession.fieldsGroupsFiles.index'),
				'translatedTitle' => __('courses::courses.courseSessions'),
				'hasCreateButton' => false,
			],
		]]];
	}
}
