<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__worker_responsibilities', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('id_worker')->constrained('courses__workers');
			$table->string('id_responsibility', 16);
			$table->foreign('id_responsibility')->references('id')->on('courses__responsibilities');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__worker_responsibilities');
	}
};
