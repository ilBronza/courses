<?php

use IlBronza\Courses\Courses;

Route::group([
	'middleware' => ['web', 'auth', 'courses.roles'],
	'prefix' => 'courses-management',
	'as' => config('courses.routePrefix')
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
	Route::get('calculate', [Courses::getController('operatorResponsibility', 'calculate'), 'calculateAll'])->name('operatorResponsibilities.calculate');
	Route::get('create', [Courses::getController('operatorResponsibility', 'create'), 'create'])->name('operatorResponsibilities.create');

	//OperatorResponsibilityValidityController
	Route::get('validate', [Courses::getController('operatorResponsibility', 'validity'), 'parse'])->name('operatorResponsibilities.checkValidity');
	Route::get('by-responsibility/{responsibility}', [Courses::getController('operatorResponsibility', 'byResponsibility'), 'index'])->name('operatorResponsibilities.byResponsibility');

	Route::post('', [Courses::getController('operatorResponsibility', 'store'), 'store'])->name('operatorResponsibilities.store');

	Route::get('{operatorResponsibility}', [Courses::getController('operatorResponsibility', 'show'), 'show'])->name('operatorResponsibilities.show');
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
