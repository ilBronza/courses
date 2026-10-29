<?php

namespace IlBronza\Courses\Traits\Certificates;

use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * Ecostudio-specific extraction. No App models or new course catalog are used.
 * A future participation implements CourseCertificateData without this trait.
 */
trait ExtractsLegacyCourseWorkerCertificateData
{
	use ResolvesCertificateFilePath;

	protected array $certificateLegacyRecords = [];

	abstract protected function getCourseWorker() : object;

	protected function getLegacyCertificateConnection() : string
	{
		return config('courses.certificates.legacy_connection', 'old');
	}

	protected function getLegacyCertificateRecord(string $table, $id, bool $required = false) : ?object
	{
		$key = $table . ':' . $id;
		if(! array_key_exists($key, $this->certificateLegacyRecords))
			$this->certificateLegacyRecords[$key] = $id === null ? null : DB::connection($this->getLegacyCertificateConnection())
				->table($table)->where('id', $id)->first();

		$record = $this->certificateLegacyRecords[$key];
		if($required && ! $record)
			throw new InvalidArgumentException("Dati attestato mancanti: record {$table} #{$id} sulla connessione legacy.");

		return $record;
	}

	public function getCourseAlias() : string
	{
		$course = $this->getLegacyCertificateRecord('courses', $this->getCourseWorker()->course_id ?? null, true);
		$alias = trim((string) ($course->alias ?? ''));
		if($alias === '')
			throw new InvalidArgumentException('Il corso legacy non ha un alias.');

		return $alias;
	}

	public function getHeaderData() : array
	{
		return [];
	}

	public function getParticipantData() : array
	{
		$participation = $this->getCourseWorker();
		$worker = $this->getLegacyCertificateRecord('workers', $participation->worker_id ?? null, true);
		$company = $this->getLegacyCertificateRecord('companies', $participation->company_id ?? null);
		$employment = null;
		if($company)
		{
			$key = 'employment:' . $company->id . ':' . $worker->id;
			if(! array_key_exists($key, $this->certificateLegacyRecords))
				$this->certificateLegacyRecords[$key] = DB::connection($this->getLegacyCertificateConnection())
					->table('company_worker')->where('company_id', $company->id)->where('worker_id', $worker->id)
					->whereNull('deleted_at')->orderByDesc('id')->first();
			$employment = $this->certificateLegacyRecords[$key];
		}

		return [
			'name' => trim(($worker->second_name ?? '') . ' ' . ($worker->first_name ?? '')),
			'tax_code' => $worker->fiscal_code ?? null,
			'company' => $company->name ?? null,
			'role' => ($employment->actual_job ?? null) ?: ($employment->job ?? null),
			'ateco' => $worker->ateco ?? $company->ateco ?? null,
		];
	}

	protected function legacyCertificateDate($value) : ?string
	{
		if($value === null || $value === '')
			return null;

		$date = $value instanceof DateTimeInterface ? $value->format('Y-m-d') : substr(trim((string) $value), 0, 10);
		if($date === '0000-00-00')
			return null;

		Validator::make(['date' => $date], ['date' => 'required|date_format:Y-m-d'])->validate();

		return $date;
	}

	public function getCourseData() : array
	{
		$participation = $this->getCourseWorker();
		$course = $this->getLegacyCertificateRecord('courses', $participation->course_id ?? null, true);
		$completedOn = $this->legacyCertificateDate($participation->completion ?? null);
		$dates = [];
		foreach(['completion_ps_t1' => 'Teoria 1', 'completion_ps_t2' => 'Teoria 2', 'completion_ps_p' => 'Pratica'] as $field => $label)
			if($date = $this->legacyCertificateDate($participation->{$field} ?? null))
				$dates[] = ['label' => $label, 'date' => $date];

		return [
			'title' => $course->name ?? null,
			'hours' => $course->hours ?? null,
			'delivery_mode' => ($participation->e_learning ?? false) ? 'e-learning' : null,
			// Enrollment is not assumed to be the actual first day of attendance.
			'starts_on' => null,
			'ends_on' => $completedOn,
			'dates' => $dates,
			'location' => $participation->venue_string ?? null,
		];
	}

	public function getDisclaimerData() : array
	{
		// Private notes and a positive assessment must never be inferred here.
		return [];
	}

	public function getSignaturesData() : array
	{
		return collect($this->getCertificateTeacherNames())->map(fn ($name) => trim($name))
			->filter(fn ($name) => $name !== '')->unique()->values()
			->map(fn ($name) => ['role' => 'Docente del corso', 'name' => $name, 'image' => null])->all();
	}

	protected function getCertificateTeacherNames() : array
	{
		return preg_split('/\R/u', (string) ($this->getCourseWorker()->teacher_string ?? '')) ?: [];
	}

	protected function getCertificateFilename() : string
	{
		$notes = trim((string) ($this->getCourseWorker()->hidden_notes ?? ''));
		if($notes === '')
			throw new InvalidArgumentException('hidden_notes è vuoto: impossibile determinare il nome dell’attestato.');

		// The importer preserves earlier notes and appends its code on the last line.
		$lines = array_values(array_filter(array_map('trim', preg_split('/\R/u', $notes) ?: []), fn ($line) => $line !== ''));

		return end($lines) ?: '';
	}
}
