<?php

namespace IlBronza\Courses\Traits\Models;

use Illuminate\Database\Eloquent\Builder;

trait OperatorResponsibilityScopesTrait
{
	public function scopeByResponsibility(Builder $query, string $responsibility) : Builder
	{
		return $query->where('responsibility_id', $responsibility);
	}

	public function scopeParsed(Builder $query) : Builder
	{
		return $query->whereNotNull('parsed_at');
	}

	public function scopeToParse(Builder $query) : Builder
	{
		return $query->whereNull('parsed_at');
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
