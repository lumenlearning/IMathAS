<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface
{
    public function findById(int $id): ?array;
}
