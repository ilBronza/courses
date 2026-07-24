<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__company_workers', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('id_company')->constrained('courses__companies');
			$table->foreignUuid('id_worker')->constrained('courses__workers');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__company_workers');
	}
};
