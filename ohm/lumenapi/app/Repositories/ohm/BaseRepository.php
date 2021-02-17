<?php
namespace App\Repositories\ohm;

class BaseRepository
{
    protected function toAssoc($data)
    {
        // Lumen returns array of objects. This silliness converts the object to json then json to associative array
        return json_decode(json_encode($data), true);
    }
}