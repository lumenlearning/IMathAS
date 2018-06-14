<?php

namespace OHM\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{

	protected $table = "imas_groups";
	protected $dateFormat = 'U';

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
	protected $fillable = ['grouptype', 'name', 'parent', 'student_pay_enabled',
		'lumen_guid'];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

}
