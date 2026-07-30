<?php

namespace IlBronza\Courses\Facades;

use Illuminate\Support\Facades\Facade;

class Courses extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() : string
	{
		return 'courses';
	}
}
