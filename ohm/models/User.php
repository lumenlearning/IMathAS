<?php

namespace OHM\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

	protected $table = "imas_users";

	/**
	 * This empty method effectively eliminates the requirement for an
	 * updated_at column.
	 *
	 * @param $value
	 */
	public function setUpdatedAtAttribute($value)
	{
	}

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [

	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = ['password'];

}
