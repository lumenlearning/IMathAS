<?php

namespace App\Repositories\ohm;

use App\Repositories\Interfaces\EnrollmentRepositoryInterface;

class EnrollmentRepository extends BaseRepository implements EnrollmentRepositoryInterface
{
    public function getById(int $enrollmentId)
    {
        $result = app('db')->select(
            'SELECT * FROM imas_students WHERE id = :enrollmentId;', ['enrollmentId' => $enrollmentId]);

        return $this->toAssoc($result[0]);
    }
}
