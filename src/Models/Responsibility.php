<?php

namespace IlBronza\Courses\Models;

class Responsibility extends CoursesPackageBaseModel
{
	static $modelConfigPrefix = 'responsibility';

	public function clientOperatorResponsibilities()
	{
		return $this->hasMany(ClientOperatorResponsibility::gpc());
	}

	public function operatorResponsibilities()
	{
		return $this->hasMany(OperatorResponsibility::gpc());
	}
}
