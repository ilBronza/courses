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
			$table->foreignUuid('course_id')->constrained('courses__courses');
			$table->string('name');
			$table->text('description')->nullable();
			$table->integer('sorting_index')->nullable();
			$table->decimal('hours', 5, 2);
			$table->decimal('minimum_hours', 5, 2);
			$table->boolean('remoteable')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__dates');
	}
};
