<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__operator_coursesessions', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('id_sessione_corso')->constrained('courses__course_sessions');
			$table->foreignUuid('id_operator')->constrained('operators__operators');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__operator_coursesessions');
	}
};
