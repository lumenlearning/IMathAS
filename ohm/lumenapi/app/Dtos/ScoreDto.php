<?php

namespace App\Dtos;

class ScoreDto extends BaseDto
{
    private $studentAnswers;
    private $studentAnswerValues;

    public function __construct($request) {
        parent::__construct($request);

        $this->studentAnswers = $request['studentAnswers'];
        $this->studentAnswerValues = $request['studentAnswerValues'];

        $this->setPostParams($request['post']);
    }

    public function getState(): array
    {
        return [
            'qsid' => [$this->questionSetId],
            'seeds' => [$this->seed],
            'stuanswers' => $this->studentAnswers,
            'stuanswersval' => $this->studentAnswerValues,
            'partattemptn' => [$this->partialAttemptNumber]
        ];
    }

    /**
     * Score is calculated against form POST parameters. Since there will be no form post,
     * answers are passed in request body then removed so as not to interfere with normal
     * scoring operation.
     */
    public function setPostParams($postParams)
    {
        foreach ($postParams as $postParam) {
            $_POST[$postParam['name']] = $postParam['value'];
        }
    }
}