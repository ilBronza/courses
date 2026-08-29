<?php

namespace IlBronza\Courses\Console\Commands;

use IlBronza\Courses\Helpers\OperatorResponsibilities\OperatorResponsibilityValidityHelper;
use IlBronza\Courses\Models\OperatorResponsibility;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CalculateOperatorResponsibilitiesValidity extends Command
{
	protected $signature = 'courses:calculate-responsibilities
		{--responsibility= : Ricalcola solo la responsabilità indicata}';

	protected $description = 'Ricalcola validità e scadenze delle responsabilità degli operatori';

	//solo le responsabilità che hanno un helper di validità configurato
	protected function getResponsibilities() : array
	{
		$responsibilities = array_keys(
			cconfig('courses.models.responsibility.helpers.validity')
		);

		if(! $responsibility = $this->option('responsibility'))
			return $responsibilities;

		return array_intersect($responsibilities, [$responsibility]);
	}

	protected function getOperatorResponsibilities(array $responsibilities) : Collection
	{
		return OperatorResponsibility::gpc()::whereIn(
			'responsibility_id',
			$responsibilities
		)->get();
	}

	public function handle() : int
	{
		$responsibilities = $this->getResponsibilities();

		$this->info('Responsabilità elaborate: ' . implode(', ', $responsibilities));

		$operatorResponsibilities = $this->getOperatorResponsibilities($responsibilities);

		$this->getOutput()->progressStart(
			$operatorResponsibilities->count()
		);

		foreach($operatorResponsibilities as $operatorResponsibility)
		{
			OperatorResponsibilityValidityHelper::parseByOperatorResponsibility($operatorResponsibility);

			$this->getOutput()->progressAdvance();
		}

		$this->getOutput()->progressFinish();

		$this->info($operatorResponsibilities->count() . ' responsabilità operatore ricalcolate.');

		return self::SUCCESS;
	}
}
