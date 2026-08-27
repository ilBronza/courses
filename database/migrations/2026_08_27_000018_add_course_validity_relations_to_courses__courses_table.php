<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::table('courses__courses', function (Blueprint $table)
		{
			$table->json('required_valid_course_ids')
				->nullable()
				->comment('Corsi che devono essere validi: [{"course_id": "uuid"}]');
			$table->json('expiration_target_course_ids')
				->nullable()
				->comment('Corsi verso cui indirizzare alla scadenza: [{"course_id": "uuid"}]');
		});
	}

	public function down()
	{
		Schema::table('courses__courses', function (Blueprint $table)
		{
			$table->dropColumn([
				'required_valid_course_ids',
				'expiration_target_course_ids',
			]);
		});
	}
};
