<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__date_sessions', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('id_tipo_data')->constrained('courses__dates');
			$table->decimal('ore', 5, 2);
			$table->dateTime('data_inizio')->nullable();
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__date_sessions');
	}
};
