<?php

namespace IlBronza\Courses\Models;

use IlBronza\CRUD\Models\PackagedBaseModel;
use IlBronza\CRUD\Traits\Model\CRUDUseUuidTrait;

class CoursesPackageBaseModel extends PackagedBaseModel
{
	use CRUDUseUuidTrait;

	static $packageConfigPrefix = 'courses';
	protected $keyType = 'string';
}
