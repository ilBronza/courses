<?php

use IlBronza\Courses\Courses;

Route::group([
	'middleware' => ['web', 'auth', 'courses.roles'],
	'prefix' => 'courses-management',
	'as' => config('courses.routePrefix'),
	'routeTranslationPrefix' => Courses::getRouteTranslationPrefix(),
	],
	function()
	{

Route::group(['prefix' => 'courses'], function()
{
	Route::get('', [Courses::getController('course', 'index'), 'index'])->name('courses.index');
	Route::get('create', [Courses::getController('course', 'create'), 'create'])->name('courses.create');
	Route::post('', [Courses::getController('course', 'store'), 'store'])->name('courses.store');

	Route::get('{course}', [Courses::getController('course', 'show'), 'show'])->name('courses.show');
	Route::get('{course}/edit', [Courses::getController('course', 'edit'), 'edit'])->name('courses.edit');
	Route::put('{course}', [Courses::getController('course', 'update'), 'update'])->name('courses.update');

	Route::delete('{course}/delete', [Courses::getController('course', 'destroy'), 'destroy'])->name('courses.destroy');
});

Route::group(['prefix' => 'course-themes'], function()
{
	Route::get('', [Courses::getController('courseTheme', 'index'), 'index'])->name('courseThemes.index');
	Route::get('create', [Courses::getController('courseTheme', 'create'), 'create'])->name('courseThemes.create');
	Route::post('', [Courses::getController('courseTheme', 'store'), 'store'])->name('courseThemes.store');

	Route::get('{courseTheme}', [Courses::getController('courseTheme', 'show'), 'show'])->name('courseThemes.show');
	Route::get('{courseTheme}/edit', [Courses::getController('courseTheme', 'edit'), 'edit'])->name('courseThemes.edit');
	Route::put('{courseTheme}', [Courses::getController('courseTheme', 'update'), 'update'])->name('courseThemes.update');

	Route::delete('{courseTheme}/delete', [Courses::getController('courseTheme', 'destroy'), 'destroy'])->name('courseThemes.destroy');
});

Route::group(['prefix' => 'date-types'], function()
{
	Route::get('', [Courses::getController('date', 'index'), 'index'])->name('dates.index');
	Route::get('create', [Courses::getController('date', 'create'), 'create'])->name('dates.create');
	Route::post('', [Courses::getController('date', 'store'), 'store'])->name('dates.store');
	Route::get('{date}', [Courses::getController('date', 'show'), 'show'])->name('dates.show');
	Route::get('{date}/edit', [Courses::getController('date', 'edit'), 'edit'])->name('dates.edit');
	Route::put('{date}', [Courses::getController('date', 'update'), 'update'])->name('dates.update');
	Route::delete('{date}/delete', [Courses::getController('date', 'destroy'), 'destroy'])->name('dates.destroy');
});

Route::group(['prefix' => 'teachers'], function()
{
	Route::get('', [Courses::getController('teacher', 'index'), 'index'])->name('teachers.index');
	Route::get('create', [Courses::getController('teacher', 'create'), 'create'])->name('teachers.create');
	Route::post('', [Courses::getController('teacher', 'store'), 'store'])->name('teachers.store');
	Route::get('{teacher}', [Courses::getController('teacher', 'show'), 'show'])->name('teachers.show');
	Route::get('{teacher}/edit', [Courses::getController('teacher', 'edit'), 'edit'])->name('teachers.edit');
	Route::put('{teacher}', [Courses::getController('teacher', 'update'), 'update'])->name('teachers.update');
	Route::delete('{teacher}/delete', [Courses::getController('teacher', 'destroy'), 'destroy'])->name('teachers.destroy');
});

Route::group(['prefix' => 'course-sessions'], function()
{
	Route::get('', [Courses::getController('courseSession', 'index'), 'index'])->name('courseSessions.index');
	Route::get('create', [Courses::getController('courseSession', 'create'), 'create'])->name('courseSessions.create');
	Route::post('', [Courses::getController('courseSession', 'store'), 'store'])->name('courseSessions.store');
	Route::get('{courseSession}', [Courses::getController('courseSession', 'show'), 'show'])->name('courseSessions.show');
	Route::get('{courseSession}/edit', [Courses::getController('courseSession', 'edit'), 'edit'])->name('courseSessions.edit');
	Route::put('{courseSession}', [Courses::getController('courseSession', 'update'), 'update'])->name('courseSessions.update');
	Route::delete('{courseSession}/delete', [Courses::getController('courseSession', 'destroy'), 'destroy'])->name('courseSessions.destroy');
});

Route::group(['prefix' => 'date-sessions'], function()
{
	Route::get('', [Courses::getController('dateSession', 'index'), 'index'])->name('dateSessions.index');
	Route::get('{dateSession}', [Courses::getController('dateSession', 'show'), 'show'])->name('dateSessions.show');
	Route::get('{dateSession}/edit', [Courses::getController('dateSession', 'edit'), 'edit'])->name('dateSessions.edit');
	Route::put('{dateSession}', [Courses::getController('dateSession', 'update'), 'update'])->name('dateSessions.update');
});

Route::group(['prefix' => 'operator-course-sessions'], function()
{
	Route::get('', [Courses::getController('operatorCourseSession', 'index'), 'index'])->name('operatorCourseSessions.index');
	Route::get('recent', [Courses::getController('operatorCourseSession', 'recent'), 'index'])->name('operatorCourseSessions.recent');
	Route::get('by-responsibility/{responsibility}', [Courses::getController('operatorCourseSession', 'byResponsibility'), 'index'])->name('operatorCourseSessions.byResponsibility');
	Route::post('certificates/download', [Courses::getController('operatorCourseSession', 'downloadCertificates'), 'downloadCertificates'])->name('operatorCourseSessions.downloadCertificates');
	Route::post('certificates/send', [Courses::getController('operatorCourseSession', 'sendCertificates'), 'sendCertificates'])->name('operatorCourseSessions.sendCertificates');
});

Route::group(['prefix' => 'course-needs'], function()
{
	Route::get('{alias}', [Courses::getController('courseNeed', 'index'), 'index'])->name('courseNeeds.index');
});

Route::group(['prefix' => 'responsibilities'], function()
{
	Route::get('', [Courses::getController('responsibility', 'index'), 'index'])->name('responsibilities.index');
	Route::get('calculate', [Courses::getController('operatorResponsibility', 'calculate'), 'calculateAll'])->name('responsibilities.calculate');
	Route::get('create', [Courses::getController('responsibility', 'create'), 'create'])->name('responsibilities.create');
	Route::post('', [Courses::getController('responsibility', 'store'), 'store'])->name('responsibilities.store');

	Route::get('{responsibility}/calculate', [Courses::getController('operatorResponsibility', 'calculate'), 'calculateByResponsibility'])->name('responsibilities.calculateByResponsibility');
	Route::get('{responsibility}', [Courses::getController('responsibility', 'show'), 'show'])->name('responsibilities.show');
	Route::get('{responsibility}/edit', [Courses::getController('responsibility', 'edit'), 'edit'])->name('responsibilities.edit');
	Route::put('{responsibility}', [Courses::getController('responsibility', 'update'), 'update'])->name('responsibilities.update');

	Route::delete('{responsibility}/delete', [Courses::getController('responsibility', 'destroy'), 'destroy'])->name('responsibilities.destroy');
});

Route::group(['prefix' => 'operator-responsibilities'], function()
{
	Route::get('', [Courses::getController('operatorResponsibility', 'index'), 'index'])->name('operatorResponsibilities.index');

	//OperatorResponsibilityCalculationController
	Route::get('calculate', [Courses::getController('operatorResponsibility', 'calculate'), 'calculateAll'])->name('operatorResponsibilities.calculate');
	Route::get('create', [Courses::getController('operatorResponsibility', 'create'), 'create'])->name('operatorResponsibilities.create');

	//OperatorResponsibilityValidityController
	Route::get('validate', [Courses::getController('operatorResponsibility', 'validity'), 'parse'])->name('operatorResponsibilities.checkValidity');
	Route::get('by-responsibility/{responsibility}', [Courses::getController('operatorResponsibility', 'byResponsibility'), 'index'])->name('operatorResponsibilities.byResponsibility');

	Route::post('', [Courses::getController('operatorResponsibility', 'store'), 'store'])->name('operatorResponsibilities.store');

	Route::get('{operatorResponsibility}', [Courses::getController('operatorResponsibility', 'show'), 'show'])->name('operatorResponsibilities.show');

	//OperatorResponsibilityCalculationController
	Route::get('{operatorResponsibility}/calculate', [Courses::getController('operatorResponsibility', 'calculate'), 'calculateByOperatorResponsibility'])->name('operatorResponsibilities.calculateByOperatorResponsibility');
	Route::get('{operatorResponsibility}/edit', [Courses::getController('operatorResponsibility', 'edit'), 'edit'])->name('operatorResponsibilities.edit');
	Route::put('{operatorResponsibility}', [Courses::getController('operatorResponsibility', 'update'), 'update'])->name('operatorResponsibilities.update');

	Route::delete('{operatorResponsibility}/delete', [Courses::getController('operatorResponsibility', 'destroy'), 'destroy'])->name('operatorResponsibilities.destroy');
});

Route::group(['prefix' => 'operators'], function()
{
	Route::get('{operator}/calculate-responsibilities', [Courses::getController('operatorResponsibility', 'calculate'), 'calculateByOperator'])->name('operators.calculateResponsibilities');
});

Route::group(['prefix' => 'client-operators'], function()
{
	Route::get('{clientOperator}/calculate-responsibilities', [Courses::getController('operatorResponsibility', 'calculate'), 'calculateByClientOperator'])->name('clientOperators.calculateResponsibilities');
});

Route::group(['prefix' => 'client-operator-responsibilities'], function()
{
	Route::get('{clientOperatorResponsibility}/calculate', [Courses::getController('operatorResponsibility', 'calculate'), 'calculateByClientOperatorResponsibility'])->name('clientOperatorResponsibilities.calculate');
});

	});
