<?php

namespace IlBronza\Courses\Tests;

use IlBronza\AccountManager\AccountManagerServiceProvider;
use IlBronza\AccountManager\Models\User;
use IlBronza\Buttons\ButtonsServiceProvider;
use IlBronza\Courses\CoursesServiceProvider;
use IlBronza\CRUD\CRUDServiceProvider;
use IlBronza\Datatables\DatatablesServiceProvider;
use IlBronza\Form\FormServiceProvider;
use IlBronza\FormField\FormFieldServiceProvider;
use IlBronza\Menu\MenuServiceProvider;
use IlBronza\UikitTemplate\UikitTemplate;
use IlBronza\UikitTemplate\UikitTemplateServiceProvider;
use IlBronza\Ukn\UknServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionServiceProvider;

abstract class TestCase extends BaseTestCase
{
	protected function getPackageProviders($app)
	{
		return [
			PermissionServiceProvider::class,
			AccountManagerServiceProvider::class,
			UknServiceProvider::class,
			MenuServiceProvider::class,
			UikitTemplateServiceProvider::class,
			ButtonsServiceProvider::class,
			FormFieldServiceProvider::class,
			FormServiceProvider::class,
			DatatablesServiceProvider::class,
			CRUDServiceProvider::class,
			CoursesServiceProvider::class,
		];
	}

	protected function defineEnvironment($app)
	{
		$app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
		$app['config']->set('app.locale', 'it');
		$app['config']->set('app.template', UikitTemplate::class);
		$app['config']->set('menu.childrenPerColumn', 8);
		$app['config']->set('menu.serviceProviders', []);
		$app['config']->set('database.default', 'testing');
		$app['config']->set('database.connections.testing', [
			'driver' => 'sqlite',
			'database' => ':memory:',
			'prefix' => '',
			'foreign_key_constraints' => true,
		]);
		$app['config']->set('auth.providers.users.model', User::class);
		$app['config']->set('activitylog.enabled', false);
		$app['config']->set('crud.broadcast.enabled', false);
		$app['config']->set('media-library.media_model', \Spatie\MediaLibrary\MediaCollections\Models\Media::class);
	}

	protected function setUp() : void
	{
		parent::setUp();

		// An in-memory database is recreated for every test; no RefreshDatabase.
		Schema::create('users', function (Blueprint $table)
		{
			$table->id();
			$table->string('name');
			$table->string('email');
			$table->string('password');
			$table->timestamps();
		});
		Schema::create('users__userdata', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->unsignedBigInteger('user_id');
			$table->string('name')->nullable();
			$table->string('surname')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});

		Schema::create('datatabledata', function (Blueprint $table)
		{
			$table->id();
			$table->unsignedBigInteger('user_id');
			$table->string('key');
			$table->json('data');
			$table->timestamps();
		});

		$permissionMigration = require __DIR__ . '/Fixtures/create_permission_tables.php';
		$permissionMigration->up();

		foreach([
			'2026_07_24_000001_create_courses__courses_table.php',
			'2026_07_29_000015_add_legacy_fields_to_courses__courses_table.php',
			'2026_08_27_000018_add_course_validity_relations_to_courses__courses_table.php',
			'2026_08_31_000019_add_archived_at_to_courses__courses_table.php',
			'2026_10_01_000021_create_courses__course_themes_table.php',
			'2026_10_03_000022_create_course_planning_tables.php',
			'2026_10_15_000023_create_teachers_and_course_session_teacher_tables.php',
		] as $filename)
			(require __DIR__ . '/../database/migrations/' . $filename)->up();

		$user = User::create(['name' => 'Tester', 'email' => 'tester@example.test', 'password' => 'secret']);
		$user->assignRole(Role::create(['name' => 'administrator', 'guard_name' => 'web']));
		$this->actingAs($user);
	}
}
