<?php

namespace OHM\Models;

use Illuminate\Database\Eloquent\Model;

class LtiUser extends Model
{

	protected $table = "imas_ltiusers";
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

}
