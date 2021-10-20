<?php

namespace App\Dtos;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

abstract class AbstractDto
{
    /**
     * Constructor.
     *
     * @param array $data An associative array. (representing a single database row)
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $data)
    {
        // We create a validator, with all the data we receive.
        // The rules come from the child class.
        $validator = Validator::make(
            $data,
            $this->configureValidatorRules()
        );

        // Validate the data we receive for this DTO.
        if (!$validator->validate()) {
            throw new InvalidArgumentException(
                'Error: ' . $validator->errors()->first()
            );
        }

        // The data is valid.
        // Now we map it.
        if (!$this->map($data)) {
            throw new InvalidArgumentException('Failed to map DTO.');
        }
    }

    /**
     * Define the rules for validating the associative array to be mapped to
     * the DTO's fields.
     *
     * @return array An associative array containing validation rules..
     * @see https://lumen.laravel.com/docs/8.x/validation
     */
    abstract protected function configureValidatorRules(): array;

    /**
     * Map an associative array to DTO fields.
     *
     * @param array $data An associative array. (representing a single database row)
     * @return bool True if mapping to DTO fields was successful.
     */
    abstract protected function map(array $data): bool;
}
