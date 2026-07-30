<?php

namespace IlBronza\Courses;

use IlBronza\CRUD\Providers\RouterProvider\RoutedObjectInterface;
use IlBronza\CRUD\Traits\IlBronzaPackages\IlBronzaPackagesTrait;

class Courses implements RoutedObjectInterface
{
	use IlBronzaPackagesTrait;

	static $packageConfigPrefix = 'courses';

	public function manageMenuButtons()
	{

	}
}
