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
			$table->foreignUuid('id_data_sessione_corso')->constrained('courses__coursesession_datesessions');
			$table->foreignUuid('id_sessione_lavoratore')->constrained('courses__course_workers');
			$table->decimal('ore', 5, 2);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__attendances');
	}
};
