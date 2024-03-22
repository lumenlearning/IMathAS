<?php

namespace App\Repositories\ohm;

use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?array
    {

        $result = app('db')->select(
            'SELECT * FROM imas_users WHERE id = :userId;', ['userId' => $id]);

        return ($result) ? $this->toAssoc($result[0]) : null;
    }
}
