<?php

use IlBronza\Courses\Courses;

Route::group([
	'middleware' => ['web', 'auth', 'courses.roles'],
	'prefix' => 'courses-management',
	'as' => config('courses.routePrefix')
	],
	function()
	{

Route::group(['prefix' => 'responsibilities'], function()
{
	Route::get('', [Courses::getController('responsibility', 'index'), 'index'])->name('responsibilities.index');
	Route::get('create', [Courses::getController('responsibility', 'create'), 'create'])->name('responsibilities.create');
	Route::post('', [Courses::getController('responsibility', 'store'), 'store'])->name('responsibilities.store');

	Route::get('{responsibility}', [Courses::getController('responsibility', 'show'), 'show'])->name('responsibilities.show');
	Route::get('{responsibility}/edit', [Courses::getController('responsibility', 'edit'), 'edit'])->name('responsibilities.edit');
	Route::put('{responsibility}', [Courses::getController('responsibility', 'update'), 'update'])->name('responsibilities.update');

	Route::delete('{responsibility}/delete', [Courses::getController('responsibility', 'destroy'), 'destroy'])->name('responsibilities.destroy');
});

	});
