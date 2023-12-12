<?php

namespace OHM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{

	protected $table = "imas_users";
	protected $dateFormat = 'U';

	/**
	 * Disable usage of the "updated_at" column. OHM currently is lacking this.
	 *
	 * @var string
	 */
	const UPDATED_AT = null;

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'id', 'groupid');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'ownerid', 'id');
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
