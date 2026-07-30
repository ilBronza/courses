<?php

namespace IlBronza\Courses\Helpers\OperatorResponsibilities;

use Exception;
use IlBronza\Courses\Models\OperatorResponsibility;

class OperatorResponsibilityValidityHelper
{
	static function parse() : void
	{
		$operatorResponsibilities = OperatorResponsibility::gpc()::query()
			->with('responsibility')
			->whereNull('completed_at')
			->whereNull('valid_to')
			->whereNotNull('errors')
			->where('valid', false)
			->orderByRaw('parsed_at IS NULL DESC')
			->orderBy('parsed_at')
			->get();

		foreach($operatorResponsibilities as $operatorResponsibility)
		{
			$responsibilityName = $operatorResponsibility->responsibility->getKey();
			$configKey = 'courses.models.responsibility.helpers.validity.' . $responsibilityName;

			if(! $helperClass = config($configKey))
				throw new Exception('configurare config(\'' . $configKey . '\')');

			$helperClass::parse($operatorResponsibility);
		}
	}
}
