<?php

namespace App\Repositories\ohm;

use App\Models\Enrollment;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;

class EnrollmentRepository extends BaseRepository implements EnrollmentRepositoryInterface
{
    public function getById(int $enrollmentId)
    {
        return Enrollment::find($enrollmentId);
    }
}
