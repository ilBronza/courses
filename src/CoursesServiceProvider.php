<?php

namespace IlBronza\Courses;

use IlBronza\CRUD\Traits\IlBronzaPackages\IlBronzaServiceProviderPackagesTrait;
use IlBronza\Courses\Console\Commands\SyncOldCourses;
use IlBronza\Courses\Http\Middleware\CoursesRoleMiddleware;
use Illuminate\Support\ServiceProvider;

class CoursesServiceProvider extends ServiceProvider
{
	use IlBronzaServiceProviderPackagesTrait;

	/**
	 * Perform post-registration booting of services.
	 *
	 * @return void
	 */
	public function boot() : void
	{
		$this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'courses');
		$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
		$this->loadRoutesFrom(__DIR__ . '/../routes/courses.php');

		$this->app['router']->aliasMiddleware('courses.roles', CoursesRoleMiddleware::class);

		// Publishing is only necessary when using the CLI.
		if ($this->app->runningInConsole())
		{
			$this->commands([
				SyncOldCourses::class,
			]);

			$this->bootForConsole();
		}
	}

	/**
	 * Register any package services.
	 *
	 * @return void
	 */
	public function register() : void
	{
		$this->mergeConfigFrom(__DIR__ . '/../config/courses.php', 'courses');

		// Register the service the package provides.
		$this->app->singleton('courses', function ($app)
		{
			return new Courses;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['courses'];
	}

	/**
	 * Console-specific booting.
	 *
	 * @return void
	 */
	protected function bootForConsole() : void
	{
		// Publishing the configuration file.
		$this->publishes([
			__DIR__ . '/../config/courses.php' => config_path('courses.php'),
		], 'courses.config');
	}
}
