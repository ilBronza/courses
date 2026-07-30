<?php

namespace IlBronza\Courses\Models;

use IlBronza\Clients\Models\Client;
class Company extends CoursesPackageBaseUuidModel
{
	static $modelConfigPrefix = 'company';
	static $deletingRelationships = ['companyWorkers'];

	public function client()
	{
		return $this->belongsTo(Client::gpc(), 'company_id');
	}

	public function companyWorkers()
	{
		return $this->hasMany(CompanyWorker::gpc());
	}
}
