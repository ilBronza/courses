<?php

namespace IlBronza\Courses\Providers;

use Illuminate\Support\ServiceProvider;

class CoursesProvider extends ServiceProvider
{
	public function boot()
	{
		$this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
	}

	public function register()
	{

	}
}
