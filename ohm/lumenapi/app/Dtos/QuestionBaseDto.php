<?php

namespace App\Dtos;

class QuestionBaseDto {
    protected $seed;
    protected $partAttemptNumber = [];
    protected $questionSetId;
    protected $options = [];

    public function __construct($request) {
        $this->seed = $request['seed'];
        $this->questionSetId = $request['questionSetId'];

        if (isset($request['partAttemptNumber'])) {
            foreach($request['partAttemptNumber'] as $part) {
                array_push($this->partAttemptNumber, $part);
            }
        }

        if (isset($request['options'])) {
            $this->options = $request['options'];
        }
    }

    /**
     * Returns response from AssessRecord
     * @param $responseData
     * @param $questionType string The question type. (number, multipart, etc)
     * @param $state
     * @param array|null $feedback An associative array of question feedback.
     * @return array
     */
    public function getResponse($responseData, string $questionType, $state, ?array $feedback): array
    {
        $response = array_merge(
            [
                'questionSetId' => $this->questionSetId,
                'questionType' => $questionType,
                'seed' => $this->seed
            ],
            $responseData,
            [
                'feedback' => $feedback
            ]
        );

        if (isset($this->options['returnState']) && $this->options['returnState']) {
            return array_merge($response, ['state' => $state]);
        }

        return $response;
    }

    /**
     * Returns question set Id passed in on request
     * @return int
     */
    public function getQuestionSetId(): int
    {
        return $this->questionSetId;
    }
}
