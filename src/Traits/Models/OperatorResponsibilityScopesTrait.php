<?php

namespace IlBronza\Courses\Traits\Models;

use Illuminate\Database\Eloquent\Builder;

trait OperatorResponsibilityScopesTrait
{
	public function scopeByResponsibility(Builder $query, string $responsibility) : Builder
	{
		return $query->where('responsibility_id', $responsibility);
	}

	public function scopeByOperator(Builder $query, string $operator) : Builder
	{
		return $query->where('operator_id', $operator);
	}

	public function scopeByClientOperator(Builder $query, string $clientOperator) : Builder
	{
		return $query->whereHas('operator.clientOperators', function (Builder $query) use ($clientOperator) {
			$query->whereKey($clientOperator);
		});
	}

	public function scopeByClient(Builder $query, string $client) : Builder
	{
		return $query->whereHas('operator.client', function (Builder $query) use ($client) {
			$query->whereKey($client);
		});
	}

	public function scopeParsed(Builder $query) : Builder
	{
		return $query->whereNotNull('parsed_at');
	}

	public function scopeToParse(Builder $query) : Builder
	{
		return $query->whereNull('parsed_at');
	}

	public function scopeAssumed(Builder $query) : Builder
	{
		return $query->where('assumed', true);
	}

	public function scopeNotAssumed(Builder $query) : Builder
	{
		return $query->where('assumed', false);
	}

	public function scopeValid(Builder $query) : Builder
	{
		return $query->where('valid', true);
	}

	public function scopeNotValid(Builder $query) : Builder
	{
		return $query->where('valid', false);
	}

	public function scopeWrong(Builder $query) : Builder
	{
		return $query->whereNotNull('errors');
	}

	public function scopeCompleted(Builder $query) : Builder
	{
		return $query->whereNotNull('completed_at');
	}

	public function scopeNotCompleted(Builder $query) : Builder
	{
		return $query->whereNull('completed_at');
	}
}
