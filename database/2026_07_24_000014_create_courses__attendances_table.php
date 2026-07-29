<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__attendances', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('coursesession_datesession_id')->constrained('courses__coursesession_datesessions');
			$table->foreignUuid('coursesession_worker_id')->constrained('courses__worker_coursesessions');
			$table->decimal('hours', 5, 2);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__attendances');
	}
};
