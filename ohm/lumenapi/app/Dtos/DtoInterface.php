<?php

namespace App\Dtos;

interface DtoInterface
{
    /**
     * Return all fields as an associative array.
     *
     * @return array An associative array containing all DTO fields.
     */
    public function toArray(): array;
}
