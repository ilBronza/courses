<?php

namespace IlBronza\Courses\Models;

class Teacher extends CoursesPackageBaseModel
{
	static $modelConfigPrefix = 'teacher';
	static $deletingRelationships = [];
	protected $fillable = ['name', 'email', 'phone', 'note'];

	public function courseSessions()
	{
		return $this->belongsToMany(CourseSession::gpc(), config('courses.models.courseSessionTeacher.table'), 'teacher_id', 'course_session_id');
	}
}
