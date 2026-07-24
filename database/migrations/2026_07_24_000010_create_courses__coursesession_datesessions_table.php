<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__coursesession_datesessions', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('id_sessione_corso')->constrained('courses__course_sessions');
			$table->foreignUuid('id_sessione_data')->constrained('courses__date_sessions');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__coursesession_datesessions');
	}
};
