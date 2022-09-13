<?php

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
    return $scoreResult;
}
