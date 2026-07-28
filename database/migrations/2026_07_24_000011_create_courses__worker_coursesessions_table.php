<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__worker_coursesessions', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('course_session_id')->constrained('courses__course_sessions');
			$table->foreignUuid('worker_id')->constrained('courses__workers');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__worker_coursesessions');
	}
};
