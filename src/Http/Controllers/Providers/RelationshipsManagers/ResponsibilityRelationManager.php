<?php

namespace IlBronza\Courses\Http\Controllers\Providers\RelationshipsManagers;

use IlBronza\CRUD\Providers\RelationshipsManager\RelationshipsManager;

class ResponsibilityRelationManager extends RelationshipsManager
{
	public function getAllRelationsParameters() : array
	{
		return [
			'show' => [
				'relations' => [
					'operatorResponsibilities' => [
						'controller' => config('courses.models.operatorResponsibility.controllers.index'),
						'fieldsGroupsParametersFile' => config('courses.models.operatorResponsibility.fieldsGroupsFiles.relatedByResponsibility'),
					],
					'clientOperatorResponsibilities' => [
						'controller' => config('courses.models.clientOperatorResponsibility.controllers.index'),
						'fieldsGroupsParametersFile' => config('courses.models.clientOperatorResponsibility.fieldsGroupsFiles.relatedByResponsibility'),
					],
				]
			]
		];
	}
}
