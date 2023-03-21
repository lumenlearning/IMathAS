<?php

namespace App\Dtos;

class QuestionBaseDto {
    protected $seed;
    protected $partAttemptNumber = [];
    protected $questionSetId;
    protected $externalId;
    protected $options = [];

    public function __construct($request) {
        $this->seed = $request['seed'];
        $this->questionSetId = $request['questionSetId'] ?? null;
        $this->externalId = $request['externalId'] ?? null;

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
                'externalId' => $this->externalId,
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
     * Returns the question's ID if provided in request.
     * @return int|null
     */
    public function getQuestionSetId(): ?int
    {
        return $this->questionSetId;
    }

    /**
     * Set the question's ID.
     */
    public function setQuestionSetId(int $id): void
    {
        $this->questionSetId = $id;
    }

    /**
     * Returns the question's external ID if provided in request.
     * @return string|null
     */
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /**
     * Set the question's external ID.
     * @param string|null $externalId
     */
    public function setExternalId(?string $externalId): void
    {
        $this->externalId = $externalId;
    }
}
