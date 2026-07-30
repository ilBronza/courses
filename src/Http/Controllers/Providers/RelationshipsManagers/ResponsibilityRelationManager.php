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
					'operatorResponsibilities' => config('courses.models.operatorResponsibility.controllers.index'),
					'clientOperatorResponsibilities' => config('courses.models.clientOperatorResponsibility.controllers.index'),
				]
			]
		];
	}
}
