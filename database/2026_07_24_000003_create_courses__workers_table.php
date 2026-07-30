<?php

use IlBronza\Operators\Models\Operator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__workers', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->uuid('worker_id')->index();
			$table->foreign('worker_id')->references('id')->on(Worker::gpc()::make()->getTable())->onDelete('cascade');
			$table->uuid('operator_id')->nullable()->index();
			$table->foreign('operator_id')->references('id')->on(Operator::gpc()::make()->getTable())->onDelete('cascade');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__workers');
	}
};
