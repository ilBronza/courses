<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up()
	{
		Schema::create('courses__client_operator_responsibilities', function (Blueprint $table)
		{
			$table->uuid('id')->primary();
			$table->foreignUuid('client_operator_id')->constrained('operators__client_operators', 'id', 'crs_cli_op_resp_cli_op_fk');
			$table->string('responsibility_id', 16);
			$table->foreign('responsibility_id', 'crs_cli_op_resp_resp_fk')->references('id')->on('courses__responsibilities');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('courses__client_operator_responsibilities');
	}
};
