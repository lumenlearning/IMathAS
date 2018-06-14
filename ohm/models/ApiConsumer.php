<?php

namespace OHM\Models;

use Illuminate\Database\Eloquent\Model;

use Ramsey\Uuid\Uuid;

class ApiConsumer extends Model
{

	protected $table = "ohm_api_consumers";
	protected $dateFormat = 'U';
	public $incrementing = false;

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
	protected $hidden = [];

	public function assignNewUuid()
	{
		$this->id = Uuid::uuid4();
	}
}
