<?php

namespace IlBronza\Courses\Http\Controllers\OperatorResponsibilities;

use IlBronza\Courses\Helpers\OperatorResponsibilities\OperatorResponsibilityValidityHelper;
use IlBronza\Courses\Models\ClientOperatorResponsibility;
use IlBronza\Courses\Models\OperatorResponsibility;
use IlBronza\Courses\Models\Responsibility;
use Illuminate\Http\Response;

class OperatorResponsibilityCalculationController extends OperatorResponsibilityCRUD
{
	public $allowedMethods = [
		'calculateAll',
		'calculateByResponsibility',
		'calculateByOperator',
		'calculateByOperatorResponsibility',
		'calculateByClientOperator',
		'calculateByClientOperatorResponsibility',
	];

	public function calculateAll() : Response
	{
		OperatorResponsibilityValidityHelper::parseAll();

		return response()->noContent();
	}

	public function calculateByResponsibility(string $responsibility) : Response
	{
		$responsibility = Responsibility::gpc()::findOrFail($responsibility);

		OperatorResponsibilityValidityHelper::parseByResponsibility($responsibility);

		return response()->noContent();
	}

	public function calculateByOperator(string $operator) : Response
	{
		$operator = config('operators.models.operator.class')::findOrFail($operator);

		OperatorResponsibilityValidityHelper::parseByOperator($operator);

		return response()->noContent();
	}

	public function calculateByOperatorResponsibility(string $operatorResponsibility) : Response
	{
		$operatorResponsibility = OperatorResponsibility::gpc()::findOrFail($operatorResponsibility);

		OperatorResponsibilityValidityHelper::parseByOperatorResponsibility($operatorResponsibility);

		return response()->noContent();
	}

	public function calculateByClientOperator(string $clientOperator) : Response
	{
		$clientOperator = config('operators.models.clientOperator.class')::findOrFail($clientOperator);

		OperatorResponsibilityValidityHelper::parseByClientOperator($clientOperator);

		return response()->noContent();
	}

	public function calculateByClientOperatorResponsibility(string $clientOperatorResponsibility) : Response
	{
		$clientOperatorResponsibility = ClientOperatorResponsibility::gpc()::findOrFail($clientOperatorResponsibility);

		OperatorResponsibilityValidityHelper::parseByClientOperatorResponsibility($clientOperatorResponsibility);

		return response()->noContent();
	}
}
