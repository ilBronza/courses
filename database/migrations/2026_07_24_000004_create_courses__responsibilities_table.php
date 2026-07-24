<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__responsibilities', function (Blueprint $table)
		{
			$table->string('id', 16)->primary();
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__responsibilities');
	}
};
