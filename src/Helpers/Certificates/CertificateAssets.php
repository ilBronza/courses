<?php

namespace IlBronza\Courses\Helpers\Certificates;

use Illuminate\Support\Str;

class CertificateAssets
{
	public static function getTeacherSignaturePath(string $name) : ?string
	{
		return static::find('signatures', $name);
	}

	public static function getOrganizationLogoPath(string $name) : ?string
	{
		return static::find('organizations', $name);
	}

	protected static function find(string $directory, string $name) : ?string
	{
		$slug = Str::slug($name);
		if($slug === '')
			return null;

		foreach(config('courses.certificates.asset_paths', []) as $root)
		{
			$path = rtrim($root, '/\\') . '/' . $directory . '/' . $slug . '.png';
			if(is_file($path) && is_readable($path))
				return $path;
		}

		return null;
	}
}
