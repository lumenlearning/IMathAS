<?php

namespace App\Dtos;

class BaseDto {
    protected $seed;
    protected $questionSetId;

    public function __construct($request) {
        $this->seed = $request['seed'];
        $this->questionSetId = $request['questionSetId'];
    }

    public function getResponse($response): array
    {
        return array_merge(
            [
                'questionSetId' => $this->questionSetId,
                'seed' => $this->seed
            ],
            $response);
    }

    public function getQuestionSetId(): int
    {
        return $this->questionSetId;
    }
}