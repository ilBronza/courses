<?php

namespace IlBronza\Courses\Helpers;

use IlBronza\Courses\Models\Course;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CourseProgram
{
	public const HOURS_RULES = 'required|numeric|decimal:0,2|min:0.01|max:999.99';

	public static function hourUnits($hours) : int
	{
		// Sum hundredths of an hour to avoid floating point comparisons.
		return (int) round((float) $hours * 100);
	}

	public static function validate(Course $course, array $program, ?Collection $types = null) : array
	{
		Validator::make(['program' => $program], [
			'program' => 'required|array|min:1',
			'program.*' => 'required|array:date_id,name,description,hours',
			'program.*.date_id' => 'required|string',
			'program.*.name' => 'required|string|max:255',
			'program.*.description' => 'nullable|string',
			'program.*.hours' => static::HOURS_RULES,
		])->validate();

		$types = ($types ?? $course->dates()->get())->keyBy('id');
		$totals = [];
		foreach($program as $index => $lesson)
		{
			if(! $types->has($lesson['date_id']))
				throw ValidationException::withMessages([
					"program.$index.date_id" => 'La tipologia deve appartenere al corso selezionato ed essere attiva.',
				]);

			$totals[$lesson['date_id']] = ($totals[$lesson['date_id']] ?? 0) + static::hourUnits($lesson['hours']);
		}

		foreach($types as $type)
			if(($totals[$type->getKey()] ?? 0) !== static::hourUnits($type->hours))
				throw ValidationException::withMessages([
					'program' => "La tipologia «{$type->name}» deve avere esattamente {$type->hours} ore, come previsto dal corso.",
				]);

		return array_values($program);
	}
}
