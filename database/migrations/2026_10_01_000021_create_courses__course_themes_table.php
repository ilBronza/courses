<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up() : void
	{
		Schema::create(config('courses.models.courseTheme.table'), function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('course_id')->constrained(config('courses.models.course.table'));
			$table->string('name');
			$table->text('description')->nullable();
			$table->json('ateco')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down() : void
	{
		Schema::dropIfExists(config('courses.models.courseTheme.table'));
	}
};
