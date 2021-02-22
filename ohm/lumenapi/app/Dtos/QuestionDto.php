<?php

namespace App\Dtos;

class QuestionDto extends BaseDto
{
    private $rawScores = [];
    private $partialAttemptNumber = [];
    private $options = [];

    public function __construct($request)
    {
        parent::__construct($request);

        if (isset($request['rawScores'])) {
            $this->rawScores = $request['rawScores'];
        }

        if (isset($request['partialAttemptNumber'])) {
            $this->partialAttemptNumber = $request['partialAttemptNumber'];

            if (isset($request['options'])) {
                $this->options = $request['options'];
            }
        }
    }

    public function getState(): array
    {
        return [
            'qsid' => [$this->questionSetId],
            'seeds' => [$this->seed],
            'rawscores' => [$this->rawScores],
            'partattemptn' => [$this->partialAttemptNumber]
        ];
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}