<?php

namespace IlBronza\Courses\Helpers\OperatorResponsibilities;

use IlBronza\Courses\Models\ClientOperatorResponsibility;
use IlBronza\Courses\Models\OperatorResponsibility;
use IlBronza\Courses\Models\Responsibility;
use IlBronza\Operators\Models\ClientOperator;
use IlBronza\Operators\Models\Operator;
use Illuminate\Support\Collection;

class OperatorResponsibilityValidityHelper
{
	static function getHelperByOperatorResponsibility(OperatorResponsibility $operatorResponsibility)
	{
		return cconfig('courses.models.responsibility.helpers.validity.' . $operatorResponsibility->getResponsibilityName());
	}

	static function parseAll() : void
	{
		static::parseCollection(
			OperatorResponsibility::gpc()::all()
		);
	}

	static function parse() : void
	{
		static::parseCollection(
			OperatorResponsibility::gpc()::toParse()
				->orderByRaw('parsed_at IS NULL DESC')
				->orderBy('parsed_at')
				->get()
		);
	}

	static function parseByResponsibility(Responsibility $responsibility) : void
	{
		static::parseCollection($responsibility->operatorResponsibilities);
	}

	static function parseByOperator(Operator $operator) : void
	{
		static::parseCollection($operator->operatorResponsibilities);
	}

	static function parseByOperatorResponsibility(OperatorResponsibility $operatorResponsibility) : void
	{
		if(! $helperClass = static::getHelperByOperatorResponsibility($operatorResponsibility))
			return;

		$helperClass::parse($operatorResponsibility);
	}

	static function parseByClientOperator(ClientOperator $clientOperator) : void
	{
		if(! $operator = $clientOperator->getOperator())
			return;

		static::parseByOperator($operator);
	}

	static function parseByClientOperatorResponsibility(ClientOperatorResponsibility $clientOperatorResponsibility) : void
	{
		if(! $clientOperator = $clientOperatorResponsibility->clientOperator)
			return;

		static::parseByClientOperator($clientOperator);
	}

	protected static function parseCollection(Collection $operatorResponsibilities) : void
	{
		foreach($operatorResponsibilities as $operatorResponsibility)
			static::parseByOperatorResponsibility($operatorResponsibility);
	}
}
