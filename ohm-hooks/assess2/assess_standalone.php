<?php

/**
 * Include the correct answers in scoring results.
 *
 * @param array $returnData The scoring data being returned.
 * @param array $scoreResult Score results from ScoreEngine->scoreResult().
 * @return array The scoring data with correct answers included.
 */
function onScoreQuestionReturn(array $returnData, array $scoreResult): array
{
    if (isset($scoreResult['extra'])) {
        $returnData['correctAnswers']['answer'] = $scoreResult['extra']['answer'];
        $returnData['correctAnswers']['answers'] = $scoreResult['extra']['answers'];
    }
    return $returnData;
}
