<?php

namespace IlBronza\Courses\Helpers\OperatorCourses;

use IlBronza\Courses\Models\Course;
use IlBronza\Operators\Models\Operator;

/**
 * Base helper for validating one course for one operator.
 *
 * Atomic validity is intentionally kept separate from the validity of an
 * operator responsibility. Concrete helpers in the host application provide
 * the business rule through isValid().
 */
abstract class OperatorCourseAtomicValidityHelper
{
	public function __construct(
		protected Operator $operator,
		protected Course $course,
	) {
	}

	abstract public function isValid() : bool;

	public function getOperator() : Operator
	{
		return $this->operator;
	}

	public function getCourse() : Course
	{
		return $this->course;
	}
}
