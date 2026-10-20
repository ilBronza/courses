<?php

namespace IlBronza\Courses\Http\Controllers\OperatorCourseSessions;

use IlBronza\Buttons\Button;
use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;
use Illuminate\Database\Eloquent\Builder;

class OperatorCourseSessionIndexController extends OperatorCourseSessionCRUD
{
	use CRUDPlainIndexTrait;
	use CRUDIndexTrait;

	public $allowedMethods = ['index'];
	public $avoidCreateButton = true;
	public $rowSelectCheckboxes = true;

	public function beforeRenderIndex()
	{
		$this->getTable()->addExtraView('top', 'courses::operatorCourseSessions.messages', [
			'tableId' => $this->getTable()->getId(),
		]);
	}

	public function addIndexButtons()
	{
		foreach([
			'downloadCertificates' => ['file-pdf', 'courses::courses.downloadCertificates'],
			'sendCertificates' => ['envelope', 'courses::courses.sendCertificates'],
		] as $action => [$icon, $text])
		{
			$button = Button::create([
				'name' => $action,
				'icon' => $icon,
				'text' => $text,
				'href' => app('courses')->route('operatorCourseSessions.' . $action),
			]);
			$button->setPrimary();
			$button->setData('tableid', $this->getTable()->getId());
			$button->setData('course-certificate-action', $action === 'downloadCertificates' ? 'download' : 'send');
			$button->setData('method', 'POST');
			$this->getTable()->getForm()->addClosureButton($button);
		}
	}

	public function getIndexFieldsArray()
	{
		return config('courses.models.operatorCourseSession.fieldsGroupsFiles.index')::getTracedFieldsGroup();
	}

	public function getRelatedFieldsArray()
	{
		return $this->getIndexFieldsArray();
	}

	public function getIndexQuery() : Builder
	{
		return $this->getModelClass()::with([
			'operator',
			'courseSession.course.responsibility',
		])->orderByDesc('created_at')->orderByDesc('id');
	}

	public function getIndexElements()
	{
		return $this->getIndexQuery()->get();
	}
}
