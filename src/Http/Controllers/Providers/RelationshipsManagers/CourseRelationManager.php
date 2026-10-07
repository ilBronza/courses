<?php

namespace IlBronza\Courses\Http\Controllers\Providers\RelationshipsManagers;

use IlBronza\CRUD\Providers\RelationshipsManager\RelationshipsManager;

class CourseRelationManager extends RelationshipsManager
{
	public function getAllRelationsParameters() : array
	{
		return [
			'show' => [
				'relations' => [
					'dates' => [
						'sorting' => false,
						'controller' => config('courses.models.date.controllers.index'),
						'fieldsGroupsParametersFile' => config('courses.models.date.fieldsGroupsFiles.index'),
						'translatedTitle' => __('courses::courses.dates'),
						'hasCreateButton' => true,
						'createButtonText' => __('courses::courses.createDate'),
						'createButtonIframe' => false,
					],
					'themes' => [
						'controller' => config('courses.models.courseTheme.controllers.index'),
						'fieldsGroupsParametersFile' => config('courses.models.courseTheme.fieldsGroupsFiles.index'),
						'translatedTitle' => __('courses::courses.courseThemes'),
						'hasCreateButton' => true,
						'createButtonText' => __('courses::courses.createCourseTheme'),
						'createButtonIframe' => false,
					],
					'courseSessions' => [
						'controller' => config('courses.models.courseSession.controllers.index'),
						'fieldsGroupsParametersFile' => config('courses.models.courseSession.fieldsGroupsFiles.index'),
						'translatedTitle' => __('courses::courses.courseSessions'),
						'hasCreateButton' => true,
						'createButtonText' => __('courses::courses.createCourseSession'),
						'createButtonIframe' => false,
					],
				],
			],
		];
	}
}
