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

    public function getAll(int $limit, int $offset)
    {
        return Enrollment::take($limit)->skip($offset)->get();
    }
}
