<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__course_sessions', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('course_id')->constrained('courses__courses');
			$table->string('name');
			$table->date('desired_start_date')->nullable();
			$table->date('desired_end_date')->nullable();
			$table->integer('min_workers')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__course_sessions');
	}
};
