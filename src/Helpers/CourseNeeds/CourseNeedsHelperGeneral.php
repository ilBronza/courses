<?php

namespace IlBronza\Courses\Helpers\CourseNeeds;

use IlBronza\Courses\Models\Course;
use IlBronza\Courses\Models\ClientOperatorResponsibility;
use IlBronza\Courses\Models\CourseNeed;
use Illuminate\Support\Collection;

/**
 * Base class for application-level helpers that generate a course-needs collection.
 */
abstract class CourseNeedsHelperGeneral
{
	public function getCollection() : Collection
	{
		$course = $this->getCourse();
		$clientOperatorResponsibilities = $this->getClientOperatorResponsibilities(
			$this->getResponsibility()
		);
		$courseWorkersByWorkerId = $this->getCourseWorkersByWorkerId(
			$clientOperatorResponsibilities,
			$course->old_course_id
		);
		$clientOperatorResponsibilitiesWithoutValidCourseWorker = $this->getClientOperatorResponsibilitiesWithoutValidCourseWorker(
			$clientOperatorResponsibilities,
			$courseWorkersByWorkerId
		);

		return $this->createCourseNeeds(
			$clientOperatorResponsibilitiesWithoutValidCourseWorker,
			$courseWorkersByWorkerId
		);
	}

	/**
	 * Create a helper for the course identified by the alias.
	 */
	public function __construct(
		protected string $alias
	) {
	}

	/**
	 * Return the alias associated with this helper instance.
	 */
	protected function getAlias() : string
	{
		return $this->alias;
	}

	/**
	 * Return the course associated with this helper alias.
	 */
	protected function getCourse() : Course
	{
		return Course::gpc()::findCachedByField('alias', $this->getAlias());
	}

	protected function getResponsibility() : string
	{
		return $this->getCourse()->common_alias;
	}

	protected function getClientOperatorResponsibilities(string $responsibility) : Collection
	{
		return ClientOperatorResponsibility::gpc()::query()
			->where('responsibility_id', $responsibility)
			->with([
				'clientOperator.operator',
				'clientOperator.client',
			])
			->get()
			->filter(fn (ClientOperatorResponsibility $clientOperatorResponsibility) =>
				$this->getWorkerId($clientOperatorResponsibility)
			)
			->values();
	}

	abstract protected function getWorkerId(
		ClientOperatorResponsibility $clientOperatorResponsibility
	) : int|string|null;

	abstract protected function getCourseWorkersByWorkerId(
		Collection $clientOperatorResponsibilities,
		int $courseId
	) : Collection;

	protected function getClientOperatorResponsibilitiesWithoutValidCourseWorker(
		Collection $clientOperatorResponsibilities,
		Collection $courseWorkersByWorkerId
	) : Collection
	{
		return $clientOperatorResponsibilities
			->reject(function (ClientOperatorResponsibility $clientOperatorResponsibility) use ($courseWorkersByWorkerId)
			{
				return $this->hasValidCourseWorker(
					$courseWorkersByWorkerId->get(
						$this->getWorkerId($clientOperatorResponsibility),
						collect()
					)
				);
			})
			->values();
	}

	protected function hasValidCourseWorker(Collection $courseWorkers) : bool
	{
		return $courseWorkers->contains(function ($courseWorker)
		{
			$expirationDate = $courseWorker->calculated_expiring_date;

			return $expirationDate?->isToday() || $expirationDate?->isFuture();
		});
	}

	protected function createCourseNeeds(
		Collection $clientOperatorResponsibilities,
		Collection $courseWorkersByWorkerId
	) : Collection
	{
		return $clientOperatorResponsibilities
			->map(fn (ClientOperatorResponsibility $clientOperatorResponsibility) =>
				$this->createCourseNeedFromClientOperatorResponsibility(
					$clientOperatorResponsibility,
					$courseWorkersByWorkerId
				)
			)
			->values();
	}

	protected function createCourseNeedFromClientOperatorResponsibility(
		ClientOperatorResponsibility $clientOperatorResponsibility,
		Collection $courseWorkersByWorkerId
	) : CourseNeed
	{
		$clientOperator = $clientOperatorResponsibility->clientOperator;
		$operator = $clientOperator->operator;
		$latestCourseWorker = $this->getLatestCourseWorker(
			$courseWorkersByWorkerId->get(
				$this->getWorkerId($clientOperatorResponsibility),
				collect()
			)
		);

		return $this->createCourseNeed([
			'id' => $clientOperatorResponsibility->getKey(),
			'operator_name' => $operator->getName(),
			'client_name' => $clientOperator->client?->getName(),
			'responsibility_name' => $clientOperatorResponsibility->getResponsibilityName(),
			'expires_at' => $latestCourseWorker?->calculated_expiring_date,
			'note' => $latestCourseWorker?->notes ?: 'Corso assente o non valido',
		]);
	}

	protected function getLatestCourseWorker(Collection $courseWorkers)
	{
		return $courseWorkers
			->sortByDesc('calculated_expiring_date')
			->first();
	}

	/**
	 * Create an in-memory course-need model for the result collection.
	 *
	 * @param array<string, mixed> $attributes
	 */
	protected function createCourseNeed(array $attributes) : CourseNeed
	{
		return CourseNeed::gpc()::make($attributes);
	}
}
