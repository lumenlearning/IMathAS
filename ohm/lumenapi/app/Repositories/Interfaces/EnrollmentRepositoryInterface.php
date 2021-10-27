<?php

namespace App\Repositories\Interfaces;

use App\Models\Enrollment;

interface EnrollmentRepositoryInterface
{
    public function getById(int $enrollmentId);

    public function getAll(int $limit, int $offset, array $columnFilters);

    public function update(Enrollment $enrollment);
}
