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
			$table->foreignUuid('id_corso')->constrained('courses__courses');
			$table->string('nome');
			$table->date('data_inizio_desiderata')->nullable();
			$table->date('data_fine_desiderata')->nullable();
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__course_sessions');
	}
};
