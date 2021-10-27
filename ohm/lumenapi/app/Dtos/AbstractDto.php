<?php

namespace App\Dtos;

use InvalidArgumentException;

abstract class AbstractDto
{
    /**
     * Constructor.
     *
     * @param array $data An associative array. (representing a single database row)
     */
    public function __construct(array $data)
    {
        if (!$this->map($data)) {
            throw new InvalidArgumentException('Failed to map DTO.');
        }
    }

    /**
     * Map an associative array to DTO fields.
     *
     * @param array $data An associative array. (representing a single database row)
     * @return bool True if mapping to DTO fields was successful.
     */
    abstract protected function map(array $data): bool;
}
