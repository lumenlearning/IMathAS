<?php

namespace App\Dtos;

class BaseDto {
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
     * @param $state
     * @return array
     */
    public function getResponse($responseData, $state): array
    {
        $response = array_merge(
            [
                'questionSetId' => $this->questionSetId,
                'seed' => $this->seed
            ],
            $responseData);

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