<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__courses', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->string('name');
			$table->string('alias')->nullable();
			$table->integer('validity_months')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__courses');
	}
};
