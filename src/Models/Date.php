<?php

namespace IlBronza\Courses\Models;

use IlBronza\Courses\Helpers\CourseProgram;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/** A course date type (e.g. theory), with its compulsory number of hours. */
class Date extends CoursesPackageBaseModel
{
	static $modelConfigPrefix = 'date';
	static $deletingRelationships = [];
	protected $fillable = ['course_id', 'name', 'description', 'hours', 'sorting_index'];
	protected $casts = ['hours' => 'decimal:2', 'sorting_index' => 'integer'];

	public function save(array $options = [])
	{
		return $this->getConnection()->transaction(function () use ($options)
		{
			Course::gpc()::whereKey($this->course_id)->lockForUpdate()->first();
			return parent::save($options);
		});
	}

	public function delete()
	{
		return $this->getConnection()->transaction(function ()
		{
			Course::gpc()::whereKey($this->course_id)->lockForUpdate()->first();
			return parent::delete();
		});
	}

	protected static function booted() : void
	{
		static::saving(function (self $type)
		{
			Validator::make($type->getAttributes(), [
				'name' => 'required|string|max:255',
				'hours' => CourseProgram::HOURS_RULES,
				'course_id' => 'required|exists:' . config('courses.models.course.table') . ',id,deleted_at,NULL',
			])->validate();

			if($type->exists && $type->isDirty('course_id'))
				throw ValidationException::withMessages(['course_id' => 'Una tipologia già creata non può essere spostata su un altro corso.']);

			if(! $type->exists || $type->isDirty('hours'))
			{
				$course = $type->course()->firstOrFail();
				$types = $course->dates()->get()->reject(fn ($item) => $item->getKey() === $type->getKey())->push($type);
				foreach($course->themes()->get() as $theme)
					if($theme->program)
						try {
							CourseProgram::validate($course, $theme->program, $types);
						} catch (ValidationException $exception) {
							throw ValidationException::withMessages(['hours' => "La modifica renderebbe incoerente il programma della tematica «{$theme->name}». Svuota prima il programma per riprogettarlo."]);
						}
			}
		});

		static::deleting(function (self $type)
		{
			foreach($type->course()->firstOrFail()->themes()->get() as $theme)
				if(collect($theme->program ?? [])->contains('date_id', $type->getKey()))
					throw ValidationException::withMessages(['date' => 'La tipologia è usata in un programma. Svuota prima il programma per riprogettarlo.']);
		});
	}

	public function course()
	{
		return $this->belongsTo(Course::gpc());
	}

	public function getCreateByRelatedUrl(Model $related) : string
	{
		return $related instanceof Course
			? $this->getCreateUrl(['course_id' => $related->getKey()])
			: parent::getCreateByRelatedUrl($related);
	}
}
