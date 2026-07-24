<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__dates', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('id_corso')->constrained('courses__courses');
			$table->string('nome');
			$table->decimal('ore', 5, 2);
			$table->decimal('ore_obbligatorie', 5, 2);
			$table->boolean('remotizzabile')->default(false);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__dates');
	}
};
