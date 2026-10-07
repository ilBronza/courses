<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up() : void
	{
		foreach(['roles', 'permissions'] as $name)
			Schema::create($name, function (Blueprint $table)
			{
				$table->id();
				$table->string('name');
				$table->string('guard_name');
				$table->timestamps();
			});

		foreach(['roles' => 'role_id', 'permissions' => 'permission_id'] as $name => $key)
			Schema::create('model_has_' . $name, function (Blueprint $table) use ($key)
			{
				$table->unsignedBigInteger($key);
				$table->string('model_type');
				$table->unsignedBigInteger('model_id');
				$table->primary([$key, 'model_id', 'model_type']);
			});

		Schema::create('role_has_permissions', function (Blueprint $table)
		{
			$table->unsignedBigInteger('permission_id');
			$table->unsignedBigInteger('role_id');
			$table->primary(['permission_id', 'role_id']);
		});
	}
};
