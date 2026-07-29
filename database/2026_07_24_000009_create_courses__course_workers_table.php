<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__course_workers', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('course_id')->constrained('courses__courses');
			$table->foreignUuid('worker_id')->constrained('courses__workers');
			$table->foreignUuid('course_session_id')->nullable()->constrained('courses__course_sessions');
			$table->string('status')->nullable();
			$table->date('completion_date')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__course_workers');
	}
};
