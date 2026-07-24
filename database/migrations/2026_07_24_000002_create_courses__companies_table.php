<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__companies', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->string('nome');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__companies');
	}
};
