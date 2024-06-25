<?php

namespace App\Repositories\Interfaces;

interface LibraryItemRepositoryInterface
{
    public function create(array $libraryItem): int;
}
