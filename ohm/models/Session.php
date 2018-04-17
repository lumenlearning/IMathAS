<?php

namespace OHM\Models;

use Illuminate\Database\Eloquent\Model;

require_once(__DIR__ . '/../../vendor/autoload.php');

class Session extends Model
{

	private $sessionid;
	private $userid;
	private $time;
	private $tzoffset;
	private $tzname;
	private $sessiondata;

	protected $table = "imas_sessions";

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
