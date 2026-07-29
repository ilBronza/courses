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
			$table->foreignUuid('company_id')->constrained('courses__companies');
			$table->foreignUuid('worker_id')->constrained('courses__workers');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__company_workers');
	}
};
