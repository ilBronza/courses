<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up() : void
	{
		Schema::create(config('courses.models.date.table'), function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('course_id')->constrained(config('courses.models.course.table'));
			$table->string('name');
			$table->text('description')->nullable();
			$table->decimal('hours', 5, 2);
			$table->unsignedInteger('sorting_index')->default(0);
			$table->timestamps();
			$table->softDeletes();
		});

		Schema::table(config('courses.models.courseTheme.table'), function (Blueprint $table)
		{
			$table->json('program')->nullable();
		});

		Schema::create(config('courses.models.courseSession.table'), function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('course_id')->constrained(config('courses.models.course.table'));
			$table->foreignUuid('course_theme_id')->constrained(config('courses.models.courseTheme.table'));
			$table->string('name');
			$table->date('desired_start_date')->nullable();
			$table->date('desired_end_date')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});

		Schema::create(config('courses.models.dateSession.table'), function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('course_session_id')->constrained(config('courses.models.courseSession.table'));
			$table->foreignUuid('date_id')->constrained(config('courses.models.date.table'));
			$table->string('type_name');
			$table->string('name');
			$table->text('description')->nullable();
			$table->decimal('hours', 5, 2);
			$table->unsignedInteger('sorting_index');
			$table->dateTime('starts_at')->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->unique(['course_session_id', 'sorting_index']);
		});
	}

	public function down() : void
	{
		Schema::dropIfExists(config('courses.models.dateSession.table'));
		Schema::dropIfExists(config('courses.models.courseSession.table'));
		Schema::table(config('courses.models.courseTheme.table'), fn (Blueprint $table) => $table->dropColumn('program'));
		Schema::dropIfExists(config('courses.models.date.table'));
	}
};
