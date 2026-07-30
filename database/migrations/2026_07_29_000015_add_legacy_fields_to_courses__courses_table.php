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
			$table->unsignedBigInteger('old_course_id')
				->nullable()
				->unique()
				->comment('Campo legacy: old.courses.id');
			$table->integer('hours')->nullable()->comment('Campo legacy: old.courses.hours');
			$table->integer('max_missing_hours')->nullable()->comment('Campo legacy: old.courses.max_missing_hours');
			$table->text('note')->nullable()->comment('Campo legacy: old.courses.note');
			$table->string('file', 32)->nullable()->comment('Campo legacy: old.courses.file');
			$table->unsignedInteger('manager_id')->nullable()->comment('Campo legacy: old.courses.manager_id');
			$table->text('description')->nullable()->comment('Campo legacy: old.courses.description');
			$table->text('description_subscribed')->nullable()->comment('Campo legacy: old.courses.description_subscribed');
			$table->string('common_alias', 32)->nullable()->comment('Campo legacy: old.courses.common_alias');
			$table->integer('child_id')->nullable()->comment('Campo legacy: old.courses.child_id');
			$table->boolean('need_parent')->nullable()->comment('Campo legacy: old.courses.need_parent');
			$table->boolean('compulsory')->nullable()->comment('Campo legacy: old.courses.compulsory');
			$table->unsignedInteger('parent_id')->nullable()->comment('Campo legacy: old.courses.parent_id');
			$table->boolean('makes_expiration_valid')->nullable()->comment('Campo legacy: old.courses.makes_expiration_valid');
			$table->double('price')->nullable()->comment('Campo legacy: old.courses.price');
			$table->integer('user_area_order')->nullable()->comment('Campo legacy: old.courses.user_area_order');
			$table->boolean('cumulative_hours')->nullable()->comment('Campo legacy: old.courses.cumulative_hours');
			$table->string('offline_course_responsibility')->nullable()->comment('Campo legacy: old.courses.offline_course_responsibility');
			$table->boolean('e_learning')->nullable()->comment('Campo legacy: old.courses.e_learning');
		});
	}

	public function down()
	{
		Schema::table('courses__courses', function (Blueprint $table)
		{
			$table->dropUnique(['old_course_id']);
			$table->dropColumn([
				'old_course_id',
				'hours',
				'max_missing_hours',
				'note',
				'file',
				'manager_id',
				'description',
				'description_subscribed',
				'common_alias',
				'child_id',
				'need_parent',
				'compulsory',
				'parent_id',
				'makes_expiration_valid',
				'price',
				'user_area_order',
				'cumulative_hours',
				'offline_course_responsibility',
				'e_learning',
			]);
		});
	}
};
