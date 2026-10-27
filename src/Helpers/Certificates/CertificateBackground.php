<?php

namespace IlBronza\Courses\Helpers\Certificates;

class CertificateBackground
{
	public static function getDefaultPath() : string
	{
		return self::normalize(config('courses.certificates.background'))
			?? dirname(__DIR__, 3) . '/resources/images/certificates/default.jpg';
	}

	public static function forCourseAlias(string $alias) : string
	{
		return self::configured('courses', $alias) ?? self::getDefaultPath();
	}

	/** Read the complete map so dots in an alias or identifier stay literal. */
	public static function configured(string $scope, string|int|null $key) : ?string
	{
		if($key === null || $key === '')
			return null;

		$profiles = config('courses.certificates.' . $scope, []);

		return self::normalize($profiles[$key]['background'] ?? null);
	}

	/** Empty overrides inherit the next level. */
	public static function normalize(?string $path) : ?string
	{
		return $path !== null && trim($path) !== '' ? $path : null;
	}
}
