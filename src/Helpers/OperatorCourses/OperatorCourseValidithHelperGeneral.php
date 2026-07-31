<?php

namespace IlBronza\Courses\Helpers\OperatorCourses;

use Carbon\Carbon;
use IlBronza\Courses\Models\OperatorResponsibility;
use Illuminate\Support\Collection;

abstract class OperatorCourseValidithHelperGeneral
{
	static string $responsibility;
	public array $problems = [];

	abstract protected function handle();

	public function __construct(
		protected OperatorResponsibility $operatorResponsibility
	) {
	}

	final static function parse(OperatorResponsibility $operatorResponsibility) : void
	{
		(new static($operatorResponsibility))->handle();
	}

	/**
	 * Ricalcola tutte le responsabilità appartenenti a una stessa tipologia.
	 *
	 * La chiave deve corrispondere a quella configurata in
	 * courses.models.responsibility.helpers.validity, ad esempio "FL".
	 */
	final static function _parseByResponsibility(string $responsibility) : void
	{
		$helperClass = cconfig(
			'courses.models.responsibility.helpers.validity.' . $responsibility
		);

		$operatorResponsibilities = OperatorResponsibility::gpc()::byResponsibility(
			$responsibility
		)->get();

		foreach($operatorResponsibilities as $operatorResponsibility)
			$helperClass::parse($operatorResponsibility);
	}

	final static function parseByResponsibility()
	{
		return static::_parseByResponsibility(static::$responsibility);
	}

	protected function getOperatorResponsibility() : OperatorResponsibility
	{
		return $this->operatorResponsibility;
	}

	public function getCourseSessions() : Collection
	{
		dd('da implementare qua poi');
	}

	public function setValidWithDate(Carbon $date)
	{
		$operatorResponsibility = $this->getOperatorResponsibility();

		$operatorResponsibility->valid = true;
		$operatorResponsibility->valid_to = $date;
		$operatorResponsibility->parsed_at = Carbon::now();
		$operatorResponsibility->errors = null;
		$operatorResponsibility->save();
	}

	public function setNotValid()
	{
		$operatorResponsibility = $this->getOperatorResponsibility();

		$operatorResponsibility->valid = false;
		$operatorResponsibility->valid_to = null;
		$operatorResponsibility->parsed_at = Carbon::now();
		$operatorResponsibility->errors = implode(" | ", $this->problems);
		$operatorResponsibility->save();
	}
}
