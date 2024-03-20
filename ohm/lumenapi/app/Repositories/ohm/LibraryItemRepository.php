<?php

namespace App\Repositories\ohm;

use App\Repositories\Interfaces\LibraryItemRepositoryInterface;

class LibraryItemRepository extends BaseRepository implements LibraryItemRepositoryInterface
{
    public function create(array $libraryItem): int
    {
        return app('db')->table('imas_library_items')->insertGetId($libraryItem);
    }
}
