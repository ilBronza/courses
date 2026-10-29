<?php

use IlBronza\Courses\Http\Controllers\Teachers\TeacherCreateStoreController;
use IlBronza\Courses\Http\Controllers\Teachers\TeacherDestroyController;
use IlBronza\Courses\Http\Controllers\Teachers\TeacherEditUpdateController;
use IlBronza\Courses\Http\Controllers\Teachers\TeacherIndexController;
use IlBronza\Courses\Http\Controllers\Teachers\TeacherShowController;
use IlBronza\Courses\Http\Controllers\Providers\FieldsGroups\TeacherFieldsGroupParametersFile;
use IlBronza\Courses\Http\Controllers\Providers\Fieldsets\TeacherFieldsetsParameters;
use IlBronza\Courses\Http\Controllers\Providers\RelationshipsManagers\TeacherRelationManager;
use IlBronza\Courses\Models\Teacher;

use IlBronza\Courses\Http\Controllers\DateSessions\DateSessionIndexController;
use IlBronza\Courses\Http\Controllers\DateSessions\DateSessionShowController;
use IlBronza\Courses\Http\Controllers\DateSessions\DateSessionEditUpdateController;
use IlBronza\Courses\Http\Controllers\Providers\FieldsGroups\DateSessionFieldsGroupParametersFile;
use IlBronza\Courses\Http\Controllers\Providers\Fieldsets\DateSessionFieldsetsParameters;

use IlBronza\Courses\Http\Controllers\CourseSessions\CourseSessionIndexController;
use IlBronza\Courses\Http\Controllers\CourseSessions\CourseSessionShowController;
use IlBronza\Courses\Http\Controllers\CourseSessions\CourseSessionEditUpdateController;
use IlBronza\Courses\Http\Controllers\CourseSessions\CourseSessionCreateStoreController;
use IlBronza\Courses\Http\Controllers\CourseSessions\CourseSessionDestroyController;
use IlBronza\Courses\Http\Controllers\OperatorCourseSessions\OperatorCourseSessionIndexController;
use IlBronza\Courses\Http\Controllers\OperatorCourseSessions\OperatorCourseSessionRecentIndexController;
use IlBronza\Courses\Http\Controllers\OperatorCourseSessions\OperatorCourseSessionByResponsibilityIndexController;
use IlBronza\Courses\Http\Controllers\OperatorCourseSessions\OperatorCourseSessionCertificateController;
use IlBronza\Courses\Http\Controllers\Providers\FieldsGroups\OperatorCourseSessionFieldsGroupParametersFile;
use IlBronza\Courses\Http\Controllers\Providers\FieldsGroups\CourseSessionFieldsGroupParametersFile;
use IlBronza\Courses\Http\Controllers\Providers\Fieldsets\CourseSessionFieldsetsParameters;

use IlBronza\Courses\Http\Controllers\Dates\DateIndexController;
use IlBronza\Courses\Http\Controllers\Dates\DateShowController;
use IlBronza\Courses\Http\Controllers\Dates\DateEditUpdateController;
use IlBronza\Courses\Http\Controllers\Dates\DateCreateStoreController;
use IlBronza\Courses\Http\Controllers\Dates\DateDestroyController;
use IlBronza\Courses\Http\Controllers\Providers\FieldsGroups\DateFieldsGroupParametersFile;
use IlBronza\Courses\Http\Controllers\Providers\Fieldsets\DateFieldsetsParameters;

use IlBronza\Courses\Http\Controllers\ClientOperatorResponsibilities\ClientOperatorResponsibilityIndexController;
use IlBronza\Courses\Http\Controllers\CourseNeeds\CourseNeedIndexController;
use IlBronza\Courses\Http\Controllers\Courses\CourseCreateStoreController;
use IlBronza\Courses\Http\Controllers\Courses\CourseDestroyController;
use IlBronza\Courses\Http\Controllers\Courses\CourseEditUpdateController;
use IlBronza\Courses\Http\Controllers\Courses\CourseIndexController;
use IlBronza\Courses\Http\Controllers\Courses\CourseShowController;
use IlBronza\Courses\Http\Controllers\CourseThemes\CourseThemeCreateStoreController;
use IlBronza\Courses\Http\Controllers\CourseThemes\CourseThemeDestroyController;
use IlBronza\Courses\Http\Controllers\CourseThemes\CourseThemeEditUpdateController;
use IlBronza\Courses\Http\Controllers\CourseThemes\CourseThemeIndexController;
use IlBronza\Courses\Http\Controllers\CourseThemes\CourseThemeShowController;
use IlBronza\Courses\Http\Controllers\Providers\FieldsGroups\CourseThemeFieldsGroupParametersFile;
use IlBronza\Courses\Http\Controllers\Providers\Fieldsets\CourseThemeFieldsetsParameters;
use IlBronza\Courses\Http\Controllers\Providers\RelationshipsManagers\CourseRelationManager;
use IlBronza\Courses\Http\Controllers\Providers\RelationshipsManagers\CourseThemeRelationManager;
use IlBronza\Courses\Http\Controllers\Providers\RelationshipsManagers\CourseSessionRelationManager;
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
use IlBronza\Courses\Http\Controllers\Providers\FieldsGroups\CourseNeedFieldsGroupParametersFile;
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
use IlBronza\Courses\Models\CourseTheme;
use IlBronza\Courses\Models\CourseNeed;
use IlBronza\Courses\Models\CourseSession;
use IlBronza\Courses\Models\CourseSessionDateSession;
use IlBronza\Courses\Models\CourseWorker;
use IlBronza\Courses\Models\Date;
use IlBronza\Courses\Models\DateSession;
use IlBronza\Courses\Models\OperatorCourseSession;
use IlBronza\Courses\Models\OperatorResponsibility;
use IlBronza\Courses\Models\Responsibility;
use IlBronza\Courses\Models\Worker;
use IlBronza\Courses\Helpers\OperatorResponsibilities\OperatorResponsibilityValidityHelper;
use IlBronza\Courses\Helpers\Certificates\CourseCertificatePdfHelper;

return [
	'routePrefix' => 'ibCourses.',

	'defaultRoles' => [
		'administrator',
		'courses',
	],

	'routeRoles' => [
	],

	'helpers' => [
		'certificates' => CourseCertificatePdfHelper::class,
		'operatorResponsibilities' => [
			'validity' => OperatorResponsibilityValidityHelper::class,
		],
	],

	'enabled' => true,

	'certificates' => [
		'legacy_connection' => 'old',
		// null uses storage_path('app/temp/course-certificates').
		'temp_directory' => null,
		'view' => 'courses::certificates.default',
		// Local image path or data URI; null uses the background bundled in the package.
		'background' => null,
		// Package logo; null or a missing file falls back to the application's logo.
		'logo' => __DIR__ . '/../resources/images/certificates/logo.jpg',
		// Optional backgrounds and logos keyed by edition UUID and theme UUID.
		'sessions' => [],
		'themes' => [],
		'defaults' => [
			'header' => ['title' => 'ATTESTATO DI FREQUENZA', 'subtitle' => null, 'organizations' => []],
			'participant' => ['tax_code' => null, 'company' => null, 'role' => null, 'ateco' => null],
			'course' => ['classification' => null, 'delivery_mode' => null, 'starts_on' => null, 'dates' => [], 'location' => null],
			'disclaimer' => ['paragraphs' => [], 'assessment' => null, 'notes' => [], 'place' => null, 'issued_on' => null],
			// null keeps the signatories extracted from the participation.
			'signatures' => null,
		],
		// Optional data overrides by course alias, without introducing subclasses.
		'courses' => [],
	],

	'models' => [
		'teacher' => [
			'table' => 'courses__teachers',
			'class' => Teacher::class,
			'relationshipsManagerClasses' => ['show' => TeacherRelationManager::class],
			'fieldsGroupsFiles' => ['index' => TeacherFieldsGroupParametersFile::class],
			'parametersFiles' => [
				'create' => TeacherFieldsetsParameters::class,
				'edit' => TeacherFieldsetsParameters::class,
				'show' => TeacherFieldsetsParameters::class,
			],
			'controllers' => [
				'index' => TeacherIndexController::class,
				'create' => TeacherCreateStoreController::class,
				'store' => TeacherCreateStoreController::class,
				'show' => TeacherShowController::class,
				'edit' => TeacherEditUpdateController::class,
				'update' => TeacherEditUpdateController::class,
				'destroy' => TeacherDestroyController::class,
			],
		],
		'courseSessionTeacher' => [
			'table' => 'courses__course_session_teacher',
		],
		'course' => [
			'table' => 'courses__courses',
			'class' => Course::class,
			'relationshipsManagerClasses' => [
				'show' => CourseRelationManager::class,
			],
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
			'helpers' => [
				// Optional subclasses of CourseCertificatePdfHelper, keyed by course alias.
				'certificates' => [],
				//classi che estendono CourseNeedsHelperGeneral, dichiarate dall'applicazione
				'needs' => [
					// 'FL_G' => \App\Helpers\Courses\Needs\FlgCourseNeedsHelper::class,
				],
			],
		],
		'courseTheme' => [
			'relationshipsManagerClasses' => ['show' => CourseThemeRelationManager::class],
			'table' => 'courses__course_themes',
			'class' => CourseTheme::class,
			'fieldsGroupsFiles' => [
				'index' => CourseThemeFieldsGroupParametersFile::class,
			],
			'parametersFiles' => [
				'create' => CourseThemeFieldsetsParameters::class,
				'edit' => CourseThemeFieldsetsParameters::class,
				'show' => CourseThemeFieldsetsParameters::class,
			],
			'controllers' => [
				'index' => CourseThemeIndexController::class,
				'create' => CourseThemeCreateStoreController::class,
				'store' => CourseThemeCreateStoreController::class,
				'show' => CourseThemeShowController::class,
				'edit' => CourseThemeEditUpdateController::class,
				'update' => CourseThemeEditUpdateController::class,
				'destroy' => CourseThemeDestroyController::class,
			],
		],
		'courseNeed' => [
			'table' => 'courses__course_needs',
			'class' => CourseNeed::class,
			'fieldsGroupsFiles' => [
				'index' => CourseNeedFieldsGroupParametersFile::class,
			],
			'controllers' => [
				'index' => CourseNeedIndexController::class,
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
			'fieldsGroupsFiles' => ['index' => DateFieldsGroupParametersFile::class],
			'parametersFiles' => [
				'create' => DateFieldsetsParameters::class,
				'edit' => DateFieldsetsParameters::class,
				'show' => DateFieldsetsParameters::class,
			],
			'controllers' => [
				'index' => DateIndexController::class,
				'show' => DateShowController::class,
				'edit' => DateEditUpdateController::class,
				'update' => DateEditUpdateController::class,
				'create' => DateCreateStoreController::class,
				'store' => DateCreateStoreController::class,
				'destroy' => DateDestroyController::class,
			],
		],
		'courseSession' => [
			'relationshipsManagerClasses' => ['show' => CourseSessionRelationManager::class],
			'table' => 'courses__course_sessions',
			'class' => CourseSession::class,
			'fieldsGroupsFiles' => ['index' => CourseSessionFieldsGroupParametersFile::class],
			'parametersFiles' => [
				'create' => CourseSessionFieldsetsParameters::class,
				'edit' => CourseSessionFieldsetsParameters::class,
				'show' => CourseSessionFieldsetsParameters::class,
			],
			'controllers' => [
				'index' => CourseSessionIndexController::class,
				'show' => CourseSessionShowController::class,
				'edit' => CourseSessionEditUpdateController::class,
				'update' => CourseSessionEditUpdateController::class,
				'create' => CourseSessionCreateStoreController::class,
				'store' => CourseSessionCreateStoreController::class,
				'destroy' => CourseSessionDestroyController::class,
			],
		],
		'dateSession' => [
			'table' => 'courses__date_sessions',
			'class' => DateSession::class,
			'fieldsGroupsFiles' => ['index' => DateSessionFieldsGroupParametersFile::class],
			'parametersFiles' => [
				'create' => DateSessionFieldsetsParameters::class,
				'edit' => DateSessionFieldsetsParameters::class,
				'show' => DateSessionFieldsetsParameters::class,
			],
			'controllers' => [
				'index' => DateSessionIndexController::class,
				'show' => DateSessionShowController::class,
				'edit' => DateSessionEditUpdateController::class,
				'update' => DateSessionEditUpdateController::class,
			],
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
		'operatorCourseSession' => [
			'table' => 'courses__operator_course_sessions',
			'class' => OperatorCourseSession::class,
			'fieldsGroupsFiles' => ['index' => OperatorCourseSessionFieldsGroupParametersFile::class],
			'controllers' => [
				'index' => OperatorCourseSessionIndexController::class,
				'recent' => OperatorCourseSessionRecentIndexController::class,
				'byResponsibility' => OperatorCourseSessionByResponsibilityIndexController::class,
				'showCertificate' => OperatorCourseSessionCertificateController::class,
				'downloadCertificates' => OperatorCourseSessionCertificateController::class,
				'sendCertificates' => OperatorCourseSessionCertificateController::class,
			],
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
