<?php

namespace IlBronza\Courses\Traits\Models;

use Illuminate\Database\Eloquent\Builder;

trait ClientOperatorResponsibilityScopesTrait
{
	public function scopeByResponsibility(Builder $query, string $responsibility) : Builder
	{
		return $query->where('responsibility_id', $responsibility);
	}

	public function scopeByClientOperator(Builder $query, string $clientOperator) : Builder
	{
		return $query->where('client_operator_id', $clientOperator);
	}

	public function scopeByOperator(Builder $query, string $operator) : Builder
	{
		return $query->whereHas('clientOperator.operator', function (Builder $query) use ($operator) {
			$query->whereKey($operator);
		});
	}

	public function scopeByClient(Builder $query, string $client) : Builder
	{
		return $query->whereHas('clientOperator.client', function (Builder $query) use ($client) {
			$query->whereKey($client);
		});
	}

	public function scopeByClientOperatorClient(Builder $query, string $client) : Builder
	{
		return $query->byClient($client);
	}
}
