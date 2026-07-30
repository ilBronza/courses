<?php

namespace IlBronza\Courses\Models;

use IlBronza\CRUD\Models\BasePivotModel;
use IlBronza\CRUD\Traits\Model\CRUDUseUuidTrait;
use IlBronza\CRUD\Traits\Model\PackagedModelsTrait;

class CoursesPackageBasePivotModel extends BasePivotModel
{
	use CRUDUseUuidTrait;
	use PackagedModelsTrait;

	static $packageConfigPrefix = 'courses';
	protected $keyType = 'string';
}
