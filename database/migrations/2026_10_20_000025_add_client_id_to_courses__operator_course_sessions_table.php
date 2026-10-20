<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up() : void
	{
		Schema::table(config('courses.models.operatorCourseSession.table'), function (Blueprint $table)
		{
			// Client IDs may change during reimports; keep the reference optional and unconstrained.
			$table->uuid('client_id')->nullable()->index();
		});
	}

	public function down() : void
	{
		Schema::table(config('courses.models.operatorCourseSession.table'), function (Blueprint $table)
		{
			$table->dropIndex(['client_id']);
			$table->dropColumn('client_id');
		});
	}
};
