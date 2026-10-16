<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up() : void
	{
		Schema::create(config('courses.models.teacher.table'), function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->string('name');
			$table->string('email')->nullable();
			$table->string('phone', 50)->nullable();
			$table->text('note')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});

		Schema::create(config('courses.models.courseSessionTeacher.table'), function (Blueprint $table)
		{
			$table->foreignUuid('course_session_id')->constrained(config('courses.models.courseSession.table'))->cascadeOnDelete();
			$table->foreignUuid('teacher_id')->constrained(config('courses.models.teacher.table'))->cascadeOnDelete();
			$table->primary(['course_session_id', 'teacher_id'], 'courses_session_teacher_primary');
		});
	}

	public function down() : void
	{
		Schema::dropIfExists(config('courses.models.courseSessionTeacher.table'));
		Schema::dropIfExists(config('courses.models.teacher.table'));
	}
};
