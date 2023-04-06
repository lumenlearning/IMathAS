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
     * @param $questionType string The question type. (number, multipart, etc)
     * @param $state
     * @param array|null $feedback An associative array of question feedback.
     * @return array
     */
    public function getQuestionResponse($responseData, string $questionType, $state, ?array $feedback)
    {
        return parent::getResponse($responseData, $questionType, $state, $feedback);
    }

    /**
     * Returns state in format expected by AssessStandalone
     * @return array
     */
    public function getState(): array
    {
        return [
            'qsid' => [$this->questionSetId],
            'uniqueid' => [$this->uniqueId],
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
