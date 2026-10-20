<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up() : void
	{
		Schema::create(config('courses.models.operatorCourseSession.table'), function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('operator_id')->constrained(config('operators.models.operator.table'));
			$table->foreignUuid('course_session_id')->constrained(config('courses.models.courseSession.table'));
			$table->string('status')->nullable();
			$table->dateTime('completed_at')->nullable();
			$table->string('result')->nullable();
			$table->text('note')->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->unique(['operator_id', 'course_session_id'], 'courses_operator_session_unique');
		});
	}

	public function down() : void
	{
		Schema::dropIfExists(config('courses.models.operatorCourseSession.table'));
	}
};
