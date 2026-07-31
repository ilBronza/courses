<?php

namespace IlBronza\Courses\Http\Controllers\OperatorResponsibilities;

use Exception;
use Illuminate\Http\Response;

class OperatorResponsibilityValidityController extends OperatorResponsibilityCRUD
{
	public $allowedMethods = [
		'parse'
	];

	public function parse() : Response
	{
		$helperClass = cconfig('courses.helpers.operatorResponsibilities.validity');

		$helperClass::parse();

		return response()->noContent();
	}
}
