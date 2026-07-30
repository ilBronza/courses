<?php

namespace IlBronza\Courses\Http\Middleware;

use IlBronza\CRUD\Middleware\CRUDBasePackageMiddlewareRolesPermissions;

class CoursesRoleMiddleware extends CRUDBasePackageMiddlewareRolesPermissions
{
	protected string $configPackageName = 'courses';
}
