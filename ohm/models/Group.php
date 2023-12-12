<?php

namespace OHM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'groupid', 'id');
    }

    public function courses(): HasManyThrough
    {
        /*
         * SELECT * FROM `imas_courses`
	     * INNER JOIN `imas_users` ON `imas_users`.`id` = `imas_courses`.`ownerid`
         * WHERE `imas_users`.`groupid` = ?
         */
        return $this->hasManyThrough(
            Course::class,
            User::class,
            'groupid',
            'ownerid',
            'id',
            'id'
        );
    }

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
	protected $hidden = ['courses'];

}
