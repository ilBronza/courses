<?php

namespace IlBronza\Courses\Helpers\Certificates;

class CertificateLogo
{
	public static function getDefaultPath() : ?string
	{
		$logo = self::normalize(config('courses.certificates.logo', dirname(__DIR__, 3) . '/resources/images/certificates/logo.jpg'));
		if($logo !== null && (self::isDataUri($logo) || (is_file($logo) && is_readable($logo))))
			return $logo;

		return self::getApplicationPath();
	}

	/** Use local application assets without making HTTP requests from PDF rendering. */
	public static function getApplicationPath() : ?string
	{
		foreach(['app.logo', 'uikittemplate.logo', 'menu.logo.path'] as $key)
		{
			$logo = self::normalize(config($key));
			if($logo === null)
				continue;
			if(self::isDataUri($logo))
				return $logo;
			if(is_file($logo) && is_readable($logo))
				return $logo;

			$url = parse_url($logo);
			if($url === false || (isset($url['scheme']) && ! in_array(strtolower($url['scheme']), ['http', 'https'], true)))
				continue;
			if(isset($url['host']) && strcasecmp($url['host'], (string) parse_url(config('app.url'), PHP_URL_HOST)) !== 0)
				continue;

			$path = realpath(public_path(ltrim(rawurldecode($url['path'] ?? ''), '/')));
			if($path && is_file($path) && is_readable($path))
				return $path;
		}

		return null;
	}

	public static function forCourseAlias(string $alias) : ?string
	{
		return self::configured('courses', $alias) ?? self::getDefaultPath();
	}

	/** Read complete maps so dots in aliases and identifiers stay literal. */
	public static function configured(string $scope, string|int|null $key) : ?string
	{
		if($key === null || $key === '')
			return null;

		$profiles = config('courses.certificates.' . $scope, []);

		return self::normalize($profiles[$key]['logo'] ?? null);
	}

	public static function normalize(?string $path) : ?string
	{
		return $path !== null && trim($path) !== '' ? $path : null;
	}

	private static function isDataUri(string $path) : bool
	{
		return (bool) preg_match('#^data:image/(png|jpeg|gif);base64,#', $path);
	}
}
