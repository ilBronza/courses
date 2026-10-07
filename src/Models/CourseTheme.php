<?php

namespace IlBronza\Courses\Models;

use IlBronza\Courses\Helpers\CourseProgram;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CourseTheme extends CoursesPackageBaseModel
{
	static $modelConfigPrefix = 'courseTheme';
	static $deletingRelationships = [];

	protected $fillable = ['course_id', 'name', 'description', 'ateco', 'program'];

	protected $casts = [
		'ateco' => 'array',
		'program' => 'array',
	];

	public function save(array $options = [])
	{
		return $this->getConnection()->transaction(function () use ($options)
		{
			// Serialize program changes with changes to the course's required hours.
			Course::gpc()::whereKey($this->course_id)->lockForUpdate()->first();
			return parent::save($options);
		});
	}

	protected static function booted() : void
	{
		static::saving(function (self $theme)
		{
			if($theme->exists && $theme->isDirty('course_id') && $theme->courseSessions()->withTrashed()->exists())
				throw ValidationException::withMessages(['course_id' => 'Una tematica usata in un’edizione non può essere spostata su un altro corso.']);

			if($theme->program && $theme->isDirty(['program', 'course_id']))
				$theme->program = CourseProgram::validate($theme->course()->firstOrFail(), $theme->program);
		});
	}

	public function courseSessions()
	{
		return $this->hasMany(CourseSession::gpc());
	}

	public function getProgramRequirementsAttribute() : string
	{
		return $this->course?->dates()->get()
			->map(fn ($type) => "{$type->name}: {$type->hours} ore")
			->implode('; ') ?: 'Definisci prima le tipologie di data del corso.';
	}

	public function getProgramStatusAttribute() : string
	{
		return $this->program ? 'Programma completo' : 'Da progettare';
	}

	public function course()
	{
		return $this->belongsTo(Course::gpc());
	}

	public function scopeByCourse(Builder $query, string $courseId) : Builder
	{
		return $query->where('course_id', $courseId);
	}

	/**
	 * ATECO entries use the JSON form format: [{"code": "..."}].
	 * Reindex rows after removal so the stored JSON remains a list.
	 */
	public function setAtecoAttribute(?array $value) : void
	{
		$this->attributes['ateco'] = $value === null ? null : $this->asJson(array_values($value));
	}

	public function getAtecoCodesAttribute() : string
	{
		return collect($this->ateco ?? [])->pluck('code')->filter()->implode(', ');
	}

	public function getCreateByRelatedUrl(Model $related) : string
	{
		if($related instanceof Course)
			return $this->getCreateUrl(['course_id' => $related->getKey()]);

		return parent::getCreateByRelatedUrl($related);
	}
}
