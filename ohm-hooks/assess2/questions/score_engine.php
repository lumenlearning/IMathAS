<?php

use IMathAS\assess2\questions\models\ScorePartResult;
use IMathAS\assess2\questions\models\ScoreQuestionParams;


/**
 * Override variables declared from question code evals before scoring.
 *
 * We are currently using this to override answer shuffling globally in OHM.
 */
function onBeforeScoreQuestion(ScoreQuestionParams $scoreQuestionParams,
                               array &$varsForScorepart,
                               array $additionalVarsForScoring
): void
{
    if (isset($GLOBALS['CFG']['GEN']['noshuffle'])) {
        $varsForScorepart['noshuffle'] = $GLOBALS['CFG']['GEN']['noshuffle'];
    }
}

/**
 * Include the correct answers in scoring results.
 *
 * @param array $scoreResult The original scoring result data.
 * @param array $varsForScorepart Bundled variables from ScoreEngine->scoreQuestion().
 * @param array $additionalVarsForScoring Bundled variables from ScoreEngine->scoreQuestion().
 * @return array The scoring result data with correct answers included.
 */
function onScoreQuestionResult(array $scoreResult,
                               array $varsForScorepart,
                               array $additionalVarsForScoring): array
{
    // In multipart, both $answer and $answers are arrays, indexed by the question part.
    $scoreResult['extra']['answer'] = $varsForScorepart['answer'] ?? null;
    $scoreResult['extra']['answers'] = $varsForScorepart['answers'] ?? null;
    $scoreResult['extra']['anstypes'] = $varsForScorepart['anstypes'] ?? null;
    $scoreResult['extra']['feedback'] = $additionalVarsForScoring['feedback'] ?? null;
    return $scoreResult;
}

/**
 * Include the correct answers in scoring results after they've been randomized.
 *
 * @param array $returnData The original array to be returned by scorePartNonMultiPart().
 * @param ?ScorePartResult $scorePartResult An instance of ScorePartResult.
 * @return array The scoring result data with correct answers included.
 */
function onScorePartMultiPart(array $returnData, ?ScorePartResult $scorePartResult): array
{
    // As part of work in OHM-1266, $scorePartResult was found to be NULL here if
    // question code evals ended prematurely due to invalid or broken question code.
    if (is_null($scorePartResult)) {
        return $returnData;
    }

    $returnData['extra'] = $scorePartResult->getExtraData();
    return $returnData;
}

/**
 * Include the correct answers in scoring results after they've been randomized.
 *
 * @param array $returnData The original array to be returned by scorePartNonMultiPart().
 * @param ScorePartResult $scorePartResult An instance of ScorePartResult.
 * @return array The scoring result data with correct answers included.
 */
function onScorePartNonMultiPart(array $returnData, ScorePartResult $scorePartResult): array
{
    $returnData['extra'] = $scorePartResult->getExtraData();
    return $returnData;
}
