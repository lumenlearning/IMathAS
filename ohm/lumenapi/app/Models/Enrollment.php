<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Enrollments map students to the courses they are enrolled in.
 *
 * Class Enrollment
 * @package App\Models
 */
class Enrollment extends Model
{

    protected $table = "imas_students";
    protected $dateFormat = 'U';

    /**
     * Disable usage of the "updated_at" column. This column does not currently exist.
     *
     * @var string
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['has_valid_access_code', 'is_opted_out_assessments'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
}
