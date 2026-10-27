<?php

namespace IlBronza\Courses\Models;

use IlBronza\Courses\Helpers\CourseProgram;
use IlBronza\Courses\Helpers\Certificates\CertificateBackground;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CourseSession extends CoursesPackageBaseModel
{
	static $modelConfigPrefix = 'courseSession';
	static $deletingRelationships = ['dateSessions'];
	protected $fillable = ['course_theme_id', 'name', 'desired_start_date', 'desired_end_date'];
	protected $casts = ['desired_start_date' => 'date', 'desired_end_date' => 'date'];
	private array $initialProgram = [];

	// A failed lesson creation must also roll back the edition.
	public function save(array $options = [])
	{
		return $this->getConnection()->transaction(fn () => parent::save($options));
	}

	protected static function booted() : void
	{
		static::saving(function (self $edition)
		{
			Validator::make($edition->getAttributes(), [
				'name' => 'required|string|max:255',
				'desired_start_date' => 'nullable|date',
				'desired_end_date' => 'nullable|date' . ($edition->desired_start_date ? '|after_or_equal:desired_start_date' : ''),
			])->validate();

			if($edition->exists)
			{
				if($edition->isDirty(['course_id', 'course_theme_id']))
					throw ValidationException::withMessages(['course_theme_id' => 'Il corso e la tematica di un’edizione già creata non possono essere cambiati.']);
				return;
			}

			$theme = CourseTheme::gpc()::find($edition->course_theme_id);
			if(! $theme || ! $course = $theme->course()->first())
				throw ValidationException::withMessages(['course_theme_id' => 'Seleziona una tematica e un corso attivi.']);

			$course->newQuery()->whereKey($course->getKey())->lockForUpdate()->firstOrFail();
			$theme = $theme->newQuery()->whereKey($theme->getKey())->lockForUpdate()->firstOrFail();
			if($theme->course_id !== $course->getKey())
				throw ValidationException::withMessages(['course_theme_id' => 'La tematica è stata spostata su un altro corso. Ricarica la pagina.']);
			try {
				$edition->initialProgram = CourseProgram::validate($course, $theme->program ?? []);
			} catch (ValidationException $exception) {
				throw ValidationException::withMessages(['course_theme_id' => 'Completa il programma della tematica rispettando le ore di ogni tipologia prima di creare l’edizione.']);
			}
			$edition->course_id = $course->getKey();
		});

		static::created(function (self $edition)
		{
			$types = $edition->course->dates()->get()->keyBy('id');
			foreach($edition->initialProgram as $index => $lesson)
				$edition->dateSessions()->create([
					'date_id' => $lesson['date_id'],
					'type_name' => $types[$lesson['date_id']]->name,
					'name' => $lesson['name'],
					'description' => $lesson['description'] ?? null,
					'hours' => $lesson['hours'],
					'sorting_index' => $index,
				]);
		});
	}

	/** Edition, theme, course type, application default, bundled default. */
	public function getCertificateBackground() : string
	{
		return CertificateBackground::configured('sessions', $this->getKey())
			?? CertificateBackground::configured('themes', $this->course_theme_id)
			?? $this->course?->getCertificateBackground()
			?? CertificateBackground::getDefaultPath();
	}

	public function course()
	{
		return $this->belongsTo(Course::gpc())->withTrashed();
	}

	public function courseTheme()
	{
		return $this->belongsTo(CourseTheme::gpc())->withTrashed();
	}

	public function dateSessions()
	{
		return $this->hasMany(DateSession::gpc())->orderBy('sorting_index');
	}

	public function operatorCourseSessions()
	{
		return $this->hasMany(OperatorCourseSession::gpc(), 'course_session_id');
	}

	public function teachers()
	{
		return $this->belongsToMany(Teacher::gpc(), config('courses.models.courseSessionTeacher.table'), 'course_session_id', 'teacher_id')
			->withTrashed()->orderBy('name');
	}

	public function relateTeachers(?array $teacherIds) : void
	{
		$teacherIds ??= [];
		// Allow keeping historical assignments, but only active teachers can be added.
		$assignedIds = $this->teachers()->allRelatedIds()->all();
		Validator::make(['teachers' => $teacherIds], [
			'teachers' => 'array',
			'teachers.*' => [
				'required', 'uuid', 'distinct',
				Rule::exists(config('courses.models.teacher.table'), 'id')->where(
					fn ($query) => $query->where(fn ($query) => $query->whereNull('deleted_at')->orWhereIn('id', $assignedIds))
				),
			],
		])->validate();
		$this->teachers()->sync($teacherIds);
		$this->unsetRelation('teachers');
	}

	public function getTeacherNamesAttribute() : string
	{
		return $this->teachers->pluck('name')->implode(', ');
	}

	public function getCreateByRelatedUrl(Model $related) : string
	{
		if($related instanceof CourseTheme)
			return $this->getCreateUrl(['course_theme_id' => $related->getKey()]);
		if($related instanceof Course)
			return $this->getCreateUrl(['course_id' => $related->getKey()]);

		return parent::getCreateByRelatedUrl($related);
	}
}
