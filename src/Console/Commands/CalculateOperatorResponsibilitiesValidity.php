<?php

namespace IlBronza\Courses\Console\Commands;

use IlBronza\Courses\Helpers\OperatorResponsibilities\OperatorResponsibilityValidityHelper;
use IlBronza\Courses\Models\OperatorResponsibility;
use IlBronza\Operators\Models\Operator;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CalculateOperatorResponsibilitiesValidity extends Command
{
	protected $signature = 'courses:calculate-responsibilities
		{--responsibility= : Ricalcola solo la responsabilità indicata}
		{--hours= : Ricalcola solo gli operatori con course_worker legacy aggiornati nelle ultime ore indicate}
		{--connection=old : Connessione del database legacy}';

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
		$query = OperatorResponsibility::gpc()::whereIn(
			'responsibility_id',
			$responsibilities
		);

		if($this->option('hours') === null)
			return $query->get();

		$hours = (int) $this->option('hours');

		if($hours < 1)
			throw new \InvalidArgumentException('L\'opzione --hours deve essere maggiore di zero.');

		$since = now()->subHours($hours);

		// Include anche i course_worker eliminati: il loro worker deve comunque
		// avere le validita' ricalcolate senza quella riga.
		$workerIds = DB::connection((string) $this->option('connection'))
			->table('course_workers')
			->where('updated_at', '>=', $since)
			->whereNotNull('worker_id')
			->distinct()
			->pluck('worker_id');

		$operatorIds = Operator::gpc()::withTrashed()
			->whereIn('old_worker_id', $workerIds)
			->pluck('id');

		$this->info(sprintf(
			'Filtro attivo: %d worker legacy aggiornati dopo %s, %d operatori collegati.',
			$workerIds->count(),
			$since->format('d/m/Y H:i'),
			$operatorIds->count(),
		));

		return $query->whereIn('operator_id', $operatorIds)->get();
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
