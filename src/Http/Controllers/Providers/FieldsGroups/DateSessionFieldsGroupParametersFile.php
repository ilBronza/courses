<?php

namespace IlBronza\Courses\Http\Controllers\Providers\FieldsGroups;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class DateSessionFieldsGroupParametersFile extends FieldsGroupParametersFile
{
	static function getFieldsGroup() : array
	{
		return [
			'translationPrefix' => 'courses::fields',
			'fields' => [
				'mySelfPrimary' => 'primary',
				'mySelfEdit' => 'links.edit',
				'mySelfSee' => 'links.see',
				'courseSession.name' => 'flat',
				'sorting_index' => 'flat',
				'type_name' => 'flat',
				'name' => 'flat',
				'hours' => 'flat',
				'starts_at' => 'dates.datetime',
			],
		];
	}
}
