<?php

namespace IlBronza\Courses;

use IlBronza\CRUD\Providers\RouterProvider\RoutedObjectInterface;
use IlBronza\CRUD\Traits\IlBronzaPackages\IlBronzaPackagesTrait;
use IlBronza\Courses\Models\Responsibility;

class Courses implements RoutedObjectInterface
{
	use IlBronzaPackagesTrait;

	static $packageConfigPrefix = 'courses';

	public function getOperatorResponsibilitiesByResponsibilityChildren() : array
	{
		return Responsibility::gpc()::orderBy('description')
			->get()
			->map(fn (Responsibility $responsibility) => [
				'name' => 'operatorResponsibilities.byResponsibility.' . $responsibility->getKey(),
				'icon' => 'user-check',
				'translatedText' => $responsibility->description ?: $responsibility->getKey(),
				'href' => $this->route('operatorResponsibilities.byResponsibility', [
					'responsibility' => $responsibility->getKey(),
				]),
			])
			->all();
	}

	public function manageMenuButtons()
	{
		if(! $menu = app('menu'))
			return;

		$settingsButton = $menu->provideSettingsButton();

		$coursesManagerButton = $menu->createButton([
			'name' => 'coursesManager',
			'icon' => 'graduation-cap',
			'text' => 'courses::courses.coursesManager'
		]);

		$coursesManagerButton->addChild(
			$menu->createButton([
				'name' => 'courses.index',
				'icon' => 'graduation-cap',
				'text' => 'courses::courses.courses',
				'href' => $this->route('courses.index')
			])
		);

		$coursesManagerButton->addChild(
			$menu->createButton([
				'name' => 'courses.responsibilities.index',
				'icon' => 'user-gear',
				'text' => 'courses::courses.responsibilities',
				'href' => $this->route('responsibilities.index')
			])
		);

		$coursesManagerButton->addChild(
			$menu->createButton([
				'name' => 'courses.operatorResponsibilities.index',
				'icon' => 'user-check',
				'text' => 'courses::courses.operatorResponsibilities',
				'href' => $this->route('operatorResponsibilities.index'),
				'children' => $this->getOperatorResponsibilitiesByResponsibilityChildren(),
			])
		);

		$coursesManagerButton->addChild(
			$menu->createButton([
				'name' => 'courses.operatorResponsibilities.checkValidity',
				'icon' => 'person-circle-check',
				'text' => 'courses::operatorResponsibilities.checkValidity',
				'href' => $this->route('operatorResponsibilities.checkValidity')
			])
		);





		$settingsButton->addChild($coursesManagerButton);
	}
}
