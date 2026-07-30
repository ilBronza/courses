<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__operator_responsibilities', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('operator_id')->constrained('operators__operators');
			$table->string('responsibility_id', 16);
			$table->foreign('responsibility_id')->references('id')->on('courses__responsibilities');
			$table->timestamp('completed_at')->nullable();
			$table->timestamp('valid_to')->nullable();
			$table->text('errors')->nullable();
			$table->boolean('valid')->default(false);
			$table->timestamp('parsed_at')->nullable();
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__operator_responsibilities');
	}
};
