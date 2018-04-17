<?php

namespace OHM\Models;

use Illuminate\Database\Eloquent\Model;

require_once(__DIR__ . '/../../vendor/autoload.php');

class User extends Model
{

	private $SID;
	private $password;
	private $rights;
	private $FirstName;
	private $LastName;
	private $email;
	private $lastaccess;
	private $groupid;
	private $msgnotify;
	private $qrightsdef;
	private $deflib;
	private $usedeflib;
	private $homelayout;
	private $hasuserimg;
	private $remoteaccess;
	private $theme;
	private $listperpage;
	private $hideonpostswidget;
	private $specialrights;
	private $FCMtoken;
	private $jsondata;
	private $forcepwreset;

	protected $table = "imas_users";

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
