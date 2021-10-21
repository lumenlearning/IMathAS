<?php

namespace App\Services\Interfaces;

interface EnrollmentServiceInterface
{
    public function getById(int $id): ?array;
}
