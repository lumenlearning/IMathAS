<?php

namespace OHM\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

	protected $table = "imas_users";

	/**
	 * Disable usage of the "updated_at" column. OHM currently is lacking this.
	 *
	 * @var string
	 */
	const UPDATED_AT = null;

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
