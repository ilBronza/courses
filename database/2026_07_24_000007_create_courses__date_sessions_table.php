<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__date_sessions', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('date_id')->constrained('courses__dates');
			$table->decimal('hours', 5, 2);
			$table->dateTime('starts_at')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__date_sessions');
	}
};
