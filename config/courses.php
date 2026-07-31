<?php

use IlBronza\Courses\Http\Controllers\ClientOperatorResponsibilities\ClientOperatorResponsibilityIndexController;
use IlBronza\Courses\Http\Controllers\Courses\CourseCreateStoreController;
use IlBronza\Courses\Http\Controllers\Courses\CourseDestroyController;
use IlBronza\Courses\Http\Controllers\Courses\CourseEditUpdateController;
use IlBronza\Courses\Http\Controllers\Courses\CourseIndexController;
use IlBronza\Courses\Http\Controllers\Courses\CourseShowController;
use IlBronza\Courses\Http\Controllers\OperatorResponsibilities\OperatorResponsibilityIndexController;
use IlBronza\Courses\Http\Controllers\OperatorResponsibilities\OperatorResponsibilityCreateStoreController;
use IlBronza\Courses\Http\Controllers\OperatorResponsibilities\OperatorResponsibilityByResponsibilityIndexController;
use IlBronza\Courses\Http\Controllers\OperatorResponsibilities\OperatorResponsibilityCalculationController;
use IlBronza\Courses\Http\Controllers\OperatorResponsibilities\OperatorResponsibilityDestroyController;
use IlBronza\Courses\Http\Controllers\OperatorResponsibilities\OperatorResponsibilityEditUpdateController;
use IlBronza\Courses\Http\Controllers\OperatorResponsibilities\OperatorResponsibilityShowController;
use IlBronza\Courses\Http\Controllers\OperatorResponsibilities\OperatorResponsibilityValidityController;
use IlBronza\Courses\Http\Controllers\Providers\FieldsGroups\ClientOperatorResponsibilityFieldsGroupParametersFile;
use IlBronza\Courses\Http\Controllers\Providers\FieldsGroups\ClientOperatorResponsibilityByClientOperatorFieldsGroupParametersFile;
use IlBronza\Courses\Http\Controllers\Providers\FieldsGroups\ClientOperatorResponsibilityByResponsibilityFieldsGroupParametersFile;
use IlBronza\Courses\Http\Controllers\Providers\FieldsGroups\CourseFieldsGroupParametersFile;
use IlBronza\Courses\Http\Controllers\Providers\FieldsGroups\OperatorResponsibilityByOperatorFieldsGroupParametersFile;
use IlBronza\Courses\Http\Controllers\Providers\FieldsGroups\OperatorResponsibilityByResponsibilityFieldsGroupParametersFile;
use IlBronza\Courses\Http\Controllers\Providers\FieldsGroups\OperatorResponsibilityFieldsGroupParametersFile;
use IlBronza\Courses\Http\Controllers\Providers\FieldsGroups\ResponsibilityFieldsGroupParametersFile;
use IlBronza\Courses\Http\Controllers\Providers\Fieldsets\ResponsibilityCreateStoreFieldsetsParameters;
use IlBronza\Courses\Http\Controllers\Providers\Fieldsets\ResponsibilityEditUpdateFieldsetsParameters;
use IlBronza\Courses\Http\Controllers\Providers\Fieldsets\CourseCreateStoreFieldsetsParameters;
use IlBronza\Courses\Http\Controllers\Providers\Fieldsets\CourseEditUpdateFieldsetsParameters;
use IlBronza\Courses\Http\Controllers\Providers\Fieldsets\OperatorResponsibilityCreateStoreFieldsetsParameters;
use IlBronza\Courses\Http\Controllers\Providers\Fieldsets\OperatorResponsibilityEditUpdateFieldsetsParameters;
use IlBronza\Courses\Http\Controllers\Providers\RelationshipsManagers\ResponsibilityRelationManager;
use IlBronza\Courses\Http\Controllers\Responsibilities\ResponsibilityCreateStoreController;
use IlBronza\Courses\Http\Controllers\Responsibilities\ResponsibilityDestroyController;
use IlBronza\Courses\Http\Controllers\Responsibilities\ResponsibilityEditUpdateController;
use IlBronza\Courses\Http\Controllers\Responsibilities\ResponsibilityIndexController;
use IlBronza\Courses\Http\Controllers\Responsibilities\ResponsibilityShowController;
use IlBronza\Courses\Models\Attendance;
use IlBronza\Courses\Models\ClientOperatorResponsibility;
use IlBronza\Courses\Models\Company;
use IlBronza\Courses\Models\CompanyWorker;
use IlBronza\Courses\Models\Course;
use IlBronza\Courses\Models\CourseSession;
use IlBronza\Courses\Models\CourseSessionDateSession;
use IlBronza\Courses\Models\CourseWorker;
use IlBronza\Courses\Models\Date;
use IlBronza\Courses\Models\DateSession;
use IlBronza\Courses\Models\OperatorResponsibility;
use IlBronza\Courses\Models\Responsibility;
use IlBronza\Courses\Models\Worker;
use IlBronza\Courses\Models\WorkerCourseSession;
use IlBronza\Courses\Helpers\OperatorResponsibilities\OperatorResponsibilityValidityHelper;

return [
	'routePrefix' => 'ibCourses.',

	'defaultRoles' => [
		'administrator',
		'courses',
	],

	'routeRoles' => [
	],

	'helpers' => [
		'operatorResponsibilities' => [
			'validity' => OperatorResponsibilityValidityHelper::class,
		],
	],

	'enabled' => true,

	'models' => [
		'course' => [
			'table' => 'courses__courses',
			'class' => Course::class,
			'fieldsGroupsFiles' => [
				'index' => CourseFieldsGroupParametersFile::class,
			],
			'parametersFiles' => [
				'create' => CourseCreateStoreFieldsetsParameters::class,
				'edit' => CourseEditUpdateFieldsetsParameters::class,
				'show' => CourseEditUpdateFieldsetsParameters::class,
			],
			'controllers' => [
				'index' => CourseIndexController::class,
				'create' => CourseCreateStoreController::class,
				'store' => CourseCreateStoreController::class,
				'show' => CourseShowController::class,
				'edit' => CourseEditUpdateController::class,
				'update' => CourseEditUpdateController::class,
				'destroy' => CourseDestroyController::class,
			],
		],
		'company' => [
			'table' => 'courses__companies',
			'class' => Company::class,
		],
		'worker' => [
			'table' => 'courses__workers',
			'class' => Worker::class,
		],
		'responsibility' => [
			'table' => 'courses__responsibilities',
			'class' => Responsibility::class,
			'fieldsGroupsFiles' => [
				'index' => ResponsibilityFieldsGroupParametersFile::class
			],
			'relationshipsManagerClasses' => [
				'show' => ResponsibilityRelationManager::class
			],
			'parametersFiles' => [
				'create' => ResponsibilityCreateStoreFieldsetsParameters::class,
				'edit' => ResponsibilityEditUpdateFieldsetsParameters::class,
				'show' => ResponsibilityEditUpdateFieldsetsParameters::class
			],
			'controllers' => [
				'index' => ResponsibilityIndexController::class,
				'create' => ResponsibilityCreateStoreController::class,
				'store' => ResponsibilityCreateStoreController::class,
				'show' => ResponsibilityShowController::class,
				'edit' => ResponsibilityEditUpdateController::class,
				'update' => ResponsibilityEditUpdateController::class,
				'destroy' => ResponsibilityDestroyController::class,
			],
			'helpers' => [
				'validity' => [
					// 'RLS' => \IlBronza\Courses\Helpers\ValidityHelpers\RlsValidityHelper::class,
				],
			],
		],
		'date' => [
			'table' => 'courses__dates',
			'class' => Date::class,
		],
		'courseSession' => [
			'table' => 'courses__course_sessions',
			'class' => CourseSession::class,
		],
		'dateSession' => [
			'table' => 'courses__date_sessions',
			'class' => DateSession::class,
		],
		'companyWorker' => [
			'table' => 'courses__company_workers',
			'class' => CompanyWorker::class,
		],
		'courseWorker' => [
			'table' => 'courses__course_workers',
			'class' => CourseWorker::class,
		],
		'coursesessionDatesession' => [
			'table' => 'courses__coursesession_datesessions',
			'class' => CourseSessionDateSession::class,
		],
		'workerCoursesession' => [
			'table' => 'courses__worker_coursesessions',
			'class' => WorkerCourseSession::class,
		],
		'attendance' => [
			'table' => 'courses__attendances',
			'class' => Attendance::class,
		],
		'clientOperatorResponsibility' => [
			'table' => 'courses__client_operator_responsibilities',
			'class' => ClientOperatorResponsibility::class,
			'fieldsGroupsFiles' => [
				'index' => ClientOperatorResponsibilityFieldsGroupParametersFile::class,
				'relatedByClientOperator' => ClientOperatorResponsibilityByClientOperatorFieldsGroupParametersFile::class,
				'relatedByResponsibility' => ClientOperatorResponsibilityByResponsibilityFieldsGroupParametersFile::class,
			],
			'controllers' => [
				'index' => ClientOperatorResponsibilityIndexController::class,
			]
		],
		'operatorResponsibility' => [
			'table' => 'courses__operator_responsibilities',
			'class' => OperatorResponsibility::class,
			'fieldsGroupsFiles' => [
				'index' => OperatorResponsibilityFieldsGroupParametersFile::class,
				'relatedByOperator' => OperatorResponsibilityByOperatorFieldsGroupParametersFile::class,
				'relatedByResponsibility' => OperatorResponsibilityByResponsibilityFieldsGroupParametersFile::class,
			],
			'parametersFiles' => [
				'create' => OperatorResponsibilityCreateStoreFieldsetsParameters::class,
				'edit' => OperatorResponsibilityEditUpdateFieldsetsParameters::class,
				'show' => OperatorResponsibilityEditUpdateFieldsetsParameters::class,
			],
			'controllers' => [
				'index' => OperatorResponsibilityIndexController::class,
				'calculate' => OperatorResponsibilityCalculationController::class,
				'byResponsibility' => OperatorResponsibilityByResponsibilityIndexController::class,
				'create' => OperatorResponsibilityCreateStoreController::class,
				'store' => OperatorResponsibilityCreateStoreController::class,
				'show' => OperatorResponsibilityShowController::class,
				'edit' => OperatorResponsibilityEditUpdateController::class,
				'update' => OperatorResponsibilityEditUpdateController::class,
				'destroy' => OperatorResponsibilityDestroyController::class,
				'validity' => OperatorResponsibilityValidityController::class,
			]
		],
	]
];
