<?php

namespace IlBronza\Courses\Providers\DatatablesFields;

use IlBronza\Datatables\DatatablesFields\Links\DatatableFieldAjaxUrl;

class DatatableFieldCalculate extends DatatableFieldAjaxUrl
{
	public $function = 'getCalculateResponsibilityUrl';
	public ?string $translationPrefix = 'courses::datatableFields';

	public $faIcon = 'calendar-check';

}