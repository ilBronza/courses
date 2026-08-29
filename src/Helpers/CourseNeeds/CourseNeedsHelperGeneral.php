<?php

namespace IlBronza\Courses\Helpers\CourseNeeds;

use IlBronza\Courses\Models\Course;
use IlBronza\Courses\Models\Responsibility;
use Illuminate\Support\Collection;

abstract class CourseNeedsHelperGeneral
{
	public string $courseAlias;
	public Course $course;
	public Responsibility $responsibility;
	public Collection $courseNeeds;

	public function __construct($courseAlias)
	{
		ini_set('memory_limit', '-1');

		$this->courseNeeds = collect();
		$this->courseAlias = $courseAlias;

		$this->setCourse();
		$this->setResponsibility();
	}

	abstract public function getCollection() : Collection;

	public function setCourse()
	{
		$this->course = Course::gpc()::byAlias($this->getCourseAlias())->first();
	}

	public function getCourse() : Course
	{
		return $this->course;
	}

	public function getResponsibilityAlias() : string
	{
		return $this->getResponsibility()->getKey();
	}

	public function setResponsibility()
	{
		$this->responsibility = $this->getCourse()->getResponsibility();
	}

	public function getResponsibility() : Responsibility
	{
		return $this->responsibility;
	}

	public function getCourseAlias() : string
	{
		return $this->courseAlias;
	}

}
