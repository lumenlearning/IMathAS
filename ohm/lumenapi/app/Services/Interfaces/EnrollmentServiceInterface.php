<?php

namespace App\Services\Interfaces;

interface EnrollmentServiceInterface
{
    public function getAll(int $pageNum, int $pageSize, array $filters): array;

    public function getById(int $id): ?array;

    public function updateById(int $id, array $input): array;
}
