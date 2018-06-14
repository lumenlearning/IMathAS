<?php

namespace OHM\Models;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class LtiCredentialScope implements Scope
{
	/**
	 * Apply the scope to a given Eloquent query builder.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder $builder
	 * @param  \Illuminate\Database\Eloquent\Model $model
	 * @return void
	 */
	public function apply(Builder $builder, Model $model)
	{
		// WHERE rights=11 OR rights=76 OR rights=77
		$builder
			->where('rights', '=', 11)
			->orWhere('rights', '=', 76)
			->orWhere('rights', '=', 77);
	}
}
