<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::table('courses__responsibilities', function (Blueprint $table)
		{
			$table->string('common_responsibility', 16)->nullable()->after('id');
		});
	}

	public function down()
	{
		Schema::table('courses__responsibilities', function (Blueprint $table)
		{
			$table->dropColumn('common_responsibility');
		});
	}
};
