<?php

namespace IlBronza\Courses\Console\Commands;

use IlBronza\Courses\Models\Course;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncOldCourses extends Command
{
	protected $signature = 'courses:sync-old-courses
		{--connection=old : Connessione del database legacy}
		{--dry-run : Mostra le operazioni senza salvare modifiche}
		{--force : Aggiorna anche i corsi invariati}
		{--chunk=500 : Numero di corsi elaborati per blocco}';

	protected $description = 'Importa old.courses in courses__courses';

	private array $summary = [
		'processed' => 0,
		'created' => 0,
		'updated' => 0,
		'skipped' => 0,
	];

	public function handle() : int
	{
		$chunkSize = max(1, (int) $this->option('chunk'));
		$dryRun = (bool) $this->option('dry-run');
		$force = (bool) $this->option('force');
		$source = DB::connection((string) $this->option('connection'));
		$target = DB::connection();

		$source->table('courses')
			->orderBy('id')
			->chunkById($chunkSize, function (Collection $courses) use ($target, $dryRun, $force) : void
			{
				$sync = function () use ($target, $courses, $dryRun, $force) : void
				{
					$existingCourses = $this->existingCourses($target, $courses);

					foreach($courses as $course)
						$this->syncCourse($target, $course, $existingCourses, $dryRun, $force);
				};

				if($dryRun)
				{
					$sync();

					return;
				}

				$target->transaction($sync);
			});

		$this->info(sprintf(
			'%s: %d corsi analizzati, %d creati, %d aggiornati, %d invariati.',
			$dryRun ? 'Simulazione completata' : 'Importazione completata',
			$this->summary['processed'],
			$this->summary['created'],
			$this->summary['updated'],
			$this->summary['skipped'],
		));

		return self::SUCCESS;
	}

	private function existingCourses(ConnectionInterface $target, Collection $courses) : array
	{
		$oldCourseIds = $courses
			->pluck('id')
			->all();

		$aliases = $courses
			->pluck('alias')
			->filter(fn ($alias) => $this->nullableString($alias) !== null)
			->unique()
			->values()
			->all();

		$query = $target->table($this->targetTable())
			->select(array_keys($this->attributes($courses->first())))
			->addSelect('id')
			->whereIn('old_course_id', $oldCourseIds);

		if($aliases !== [])
			$query->orWhereIn('alias', $aliases);

		$byOldCourseId = [];
		$byAlias = [];

		foreach($query->get() as $course)
		{
			if($course->old_course_id !== null)
				$byOldCourseId[(string) $course->old_course_id] = $course;

			if(($alias = $this->nullableString($course->alias)) !== null)
				$byAlias[$alias][] = $course;
		}

		return compact('byOldCourseId', 'byAlias');
	}

	private function syncCourse(
		ConnectionInterface $target,
		object $course,
		array &$existingCourses,
		bool $dryRun,
		bool $force,
	) : void
	{
		$this->summary['processed']++;

		$oldCourseId = (string) $course->id;
		$alias = $this->nullableString($course->alias);
		$existingCourse = $existingCourses['byOldCourseId'][$oldCourseId]
			?? (($alias !== null && count($existingCourses['byAlias'][$alias] ?? []) === 1)
				? $existingCourses['byAlias'][$alias][0]
				: null);
		$attributes = $this->attributes($course);

		if($existingCourse === null)
		{
			$id = (string) Str::uuid();

			if(! $dryRun)
				$target->table($this->targetTable())->insert(['id' => $id] + $attributes);

			$createdCourse = (object) (['id' => $id] + $attributes);
			$existingCourses['byOldCourseId'][$oldCourseId] = $createdCourse;

			if($alias !== null)
				$existingCourses['byAlias'][$alias][] = $createdCourse;

			$this->summary['created']++;

			return;
		}

		if((! $force) && $this->isUpToDate($existingCourse, $attributes))
		{
			$this->summary['skipped']++;

			return;
		}

		if(! $dryRun)
			$target->table($this->targetTable())
				->where('id', $existingCourse->id)
				->update($attributes);

		foreach($attributes as $column => $value)
			$existingCourse->{$column} = $value;

		$this->summary['updated']++;
	}

	private function attributes(object $course) : array
	{
		return [
			'old_course_id' => $course->id,
			'name' => $course->name,
			'alias' => $course->alias,
			'validity_months' => $course->month_validity,
			'hours' => $course->hours,
			'max_missing_hours' => $course->max_missing_hours,
			'note' => $course->note,
			'file' => $course->file,
			'manager_id' => $course->manager_id,
			'description' => $course->description,
			'description_subscribed' => $course->description_subscribed,
			'common_alias' => $course->common_alias,
			'child_id' => $course->child_id,
			'need_parent' => $course->need_parent,
			'compulsory' => $course->compulsory,
			'parent_id' => $course->parent_id,
			'makes_expiration_valid' => $course->makes_expiration_valid,
			'price' => $course->price,
			'user_area_order' => $course->user_area_order,
			'cumulative_hours' => $course->cumulative_hours,
			'offline_course_responsibility' => $course->offline_course_responsibility,
			'e_learning' => $course->e_learning,
			'created_at' => $course->created_at,
			'updated_at' => $course->updated_at,
			'deleted_at' => $course->deleted_at,
		];
	}

	private function isUpToDate(object $existingCourse, array $attributes) : bool
	{
		foreach($attributes as $column => $value)
			if(! $this->valuesAreEqual($existingCourse->{$column} ?? null, $value))
				return false;

		return true;
	}

	private function valuesAreEqual(mixed $targetValue, mixed $legacyValue) : bool
	{
		if($targetValue === null || $legacyValue === null)
			return $targetValue === $legacyValue;

		return (string) $targetValue === (string) $legacyValue;
	}

	private function targetTable() : string
	{
		$class = Course::gpc();

		return (new $class)->getTable();
	}

	private function nullableString(mixed $value) : ? string
	{
		if($value === null)
			return null;

		$value = trim((string) $value);

		return $value === '' ? null : $value;
	}
}
