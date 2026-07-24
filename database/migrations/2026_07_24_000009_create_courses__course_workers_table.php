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
			$table->foreignUuid('id_course')->constrained('courses__courses');
			$table->foreignUuid('id_worker')->constrained('courses__workers');
			$table->foreignUuid('id_course_session')->nullable()->constrained('courses__course_sessions');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__course_workers');
	}
};
