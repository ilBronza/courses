<?php

namespace IlBronza\Courses\Models;

use IlBronza\Courses\Helpers\Certificates\CertificateAssets;

class Teacher extends CoursesPackageBaseModel
{
	static $modelConfigPrefix = 'teacher';
	static $deletingRelationships = [];
	protected $fillable = ['name', 'email', 'phone', 'note'];

	public function getSignatureImagePath() : ?string
	{
		return CertificateAssets::getTeacherSignaturePath((string) $this->name);
	}

	public function courseSessions()
	{
		return $this->belongsToMany(CourseSession::gpc(), config('courses.models.courseSessionTeacher.table'), 'teacher_id', 'course_session_id');
	}
}
