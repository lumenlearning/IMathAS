<?php

namespace App\Dtos;

use App\Exceptions\MissingIdException;

class QuestionBaseDto {
    protected $seed;
    protected $partAttemptNumber = [];
    protected $questionSetId;
    protected $uniqueId;
    protected $options = [];

    public function __construct($request) {
        if (empty($request['questionSetId']) && empty($request['uniqueId'])) {
            throw new MissingIdException("One question ID must be specified: questionSetId or uniqueId");
        }

        $this->seed = $request['seed'];
        $this->questionSetId = $request['questionSetId'] ?? null;
        $this->uniqueId = $request['uniqueId'] ?? null;

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
        $uniqueId32 = base_convert($this->uniqueId, 10, 32);

        $response = array_merge(
            [
                'questionSetId' => $this->questionSetId,
                'ohmUniqueId' => $uniqueId32,
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
     * Returns the question's uniqueid ID if provided in request.
     * @return string|null
     */
    public function getUniqueId(): ?string
    {
        return $this->uniqueId;
    }

    /**
     * Set the question's uniqueid ID.
     * @param string|null $uniqueId
     */
    public function setUniqueId(?string $uniqueId): void
    {
        $this->uniqueId = $uniqueId;
    }
}
