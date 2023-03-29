<?php

namespace App\Dtos;

use Illuminate\Support\Facades\Log;

class QuestionScoreDto extends QuestionBaseDto
{
    private $studentAnswers;
    private $studentAnswerValues;
    private $partsToScore = true;
    private $control;
    private $postParams;

    public function __construct($request) {
        parent::__construct($request);

        $this->studentAnswers = $request['studentAnswers'];
        $this->studentAnswerValues = $request['studentAnswerValues'];

        if (isset($request['partsToScore'])) {
            $this->partsToScore = $request['partsToScore'];
        }

        $this->setPostParams($request['post']);
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
            'stuanswers' => $this->studentAnswers,
            'stuanswersval' => $this->studentAnswerValues,
            'partattemptn' => [$this->partAttemptNumber]
        ];
    }

    /**
     * Returns parts to score passed in on request
     * @return bool|mixed
     */
    public function getPartsToScore()
    {
        return $this->partsToScore;
    }

    /**
     * Calculated answer weight from question control.
     * This code was copied from sections of the ScoreEngine class.
     * @param $questionControl
     * @return array|int[]
     */
    public function getAnswerWeights($questionControl)
    {
        $answeights = null;
        $anstypes = null;

        $answerWeights = null;

        try {
            // php interpret data in control block and set params $anstypes and $answeights accordingly
            eval(interpret('control', 'multipart', $questionControl));

            if (isset($anstypes)) {
                if (!is_array($anstypes)) {
                    $anstypes = explode(",", $anstypes);
                }
                $anstypes = array_map('trim', $anstypes);
            }

            if (isset($answeights)) {
                if (!is_array($answeights)) {
                    $answerWeights = explode(",",$answeights);
                }
                $answerWeights = array_map('trim', $answeights);
                if (count($answeights) != count($anstypes)) {
                    $answerWeights = array_fill(0, count($anstypes), 1);
                }
            } else {
                if (count($anstypes)>1) {
                    $answerWeights = array_fill(0, count($anstypes), 1);
                } else {
                    $answerWeights = array(1);
                }
            }
        } catch (\Throwable $t) {
            Log::error($t);
            return null;
        }

        return $answerWeights;
    }

    /**
     * Returns response data for scored question
     * @param $responseData
     * @param $questionData
     * @param $state
     * @param array|null $feedback An associative array of question feedback.
     * @return array
     */
    public function getScoreResponse($responseData, $questionData, $state, ?array $feedback): array
    {
        $response = parent::getResponse($responseData, $questionData['questionType'], $state, $feedback);

        if ($questionData['questionType'] === 'multipart') {
            $answerWeight = $this->getAnswerWeights($questionData['questionControl']);
            if ($answerWeight) {
                return array_merge($response, ['answerWeights' => $answerWeight]);
            }
        }

        return $response;
    }

    /**
     * Score is calculated against form POST parameters. Since there will be no form post,
     * answers are passed in request body then removed so as not to interfere with normal
     * scoring operation.
     */
    public function setPostParams($postParams)
    {
        $this->postParams = [];
        foreach ($postParams as $postParam) {
            $_POST[$postParam['name']] = $postParam['value'];
            $_POST[$postParam['name'] . '-val'] = $postParam['value-decimal'] ?? null;
            $this->postParams[$postParam['name']] = $postParam['value'];
        }
    }

    public function getPostParams()
    {
        return $this->postParams;
    }
}
