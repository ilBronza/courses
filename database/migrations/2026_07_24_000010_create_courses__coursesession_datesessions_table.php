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
			$table->foreignUuid('course_session_id')->constrained('courses__course_sessions');
			$table->foreignUuid('date_session_id')->constrained('courses__date_sessions');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__coursesession_datesessions');
	}
};
