<?php

namespace App\Dtos;

class QuestionDto extends QuestionBaseDto
{
    private $rawScores = [];

    public function __construct($request)
    {
        parent::__construct($request);

        if (isset($request['rawScores'])) {
            $this->rawScores = $request['rawScores'];
        }

        if (isset($request['partialAttemptNumber'])) {
            $this->partialAttemptNumber = $request['partialAttemptNumber'];
        }

        if (isset($request['options'])) {
            $this->options = $request['options'];
        }
    }

    /**
     * Returns response data for question
     * @param $responseData
     * @param $state
     * @return array
     */
    public function getQuestionResponse($responseData, $state)
    {
        return parent::getResponse($responseData, $state);
    }

    /**
     * Returns state in format expected by AssessStandalone
     * @return array
     */
    public function getState(): array
    {
        return [
            'qsid' => [$this->questionSetId],
            'seeds' => [$this->seed],
            'rawscores' => [$this->rawScores],
            'partattemptn' => [$this->partAttemptNumber]
        ];
    }

    /**
     * Return request options
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
