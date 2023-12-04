<?php

namespace OHM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{

    protected $table = "imas_courses";
    protected $dateFormat = 'U';

    /**
     * Disable usage of the "updated_at" column. OHM currently is lacking this.
     *
     * @var string
     */
    const UPDATED_AT = null;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'ownerid');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['student_pay_enabled'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

}
