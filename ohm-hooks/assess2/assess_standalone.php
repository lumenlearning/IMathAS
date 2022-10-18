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
    if (!isset($scoreResult['extra'])) {
        return $returnData;
    }

    $returnData['correctAnswers']['answer'] = $scoreResult['extra']['answer'];
    $returnData['correctAnswers']['answers'] = $scoreResult['extra']['answers'];

    /*
     * For questions whose answers have been randomized to minimize cheating,
     * we need to return the correct answers *after* they've been rearranged
     * using the question seed.
     *
     * The existence of "randomAnswerKeys" indicates we need to map the original
     * correct answer index(es) to the rearranged answer index(es).
     */

    // Handle randomized "multans" type question answers.
    if (
        _hasCorrectAnswers($scoreResult)
        && !_hasCorrectAnswer($scoreResult)
        && _hasRandomAnswerKeys($scoreResult)
    ) {
        $originalCorrectAnswers = _correctAnswersAsArray($scoreResult['extra']['answers']);

        $randomizedCorrectAnswers = [];
        foreach ($scoreResult['extra']['lumenlearning']['randomAnswerKeys'] as $randomAnswerKey => $originalAnswerKey) {
            if (in_array($originalAnswerKey, $originalCorrectAnswers)) {
                array_push($randomizedCorrectAnswers, $randomAnswerKey);
            }
        }

        // Replace the original answers with the randomized answers.
        $returnData['correctAnswers']['answers'] = implode(',', $randomizedCorrectAnswers);
    }

    // Handle randomized "choices" type question answers.
    if (
        _hasCorrectAnswer($scoreResult)
        && !_hasCorrectAnswers($scoreResult)
        && _hasRandomAnswerKeys($scoreResult)
    ) {
        $originalCorrectAnswer = (int)$scoreResult['extra']['answer'];

        $randomizedCorrectAnswer = array_search($originalCorrectAnswer,
            $scoreResult['extra']['lumenlearning']['randomAnswerKeys']);

        // Replace the original answers with the randomized answers.
        $returnData['correctAnswers']['answer'] = $randomizedCorrectAnswer;
    }

    return $returnData;
}

/*
 * "Private" functions follow; not for use outside of this PHP file.
 */

/**
 * Determine if score results has a correct answer.
 *
 * Note: This is different than having correct, multiple answers.
 *
 * @param array $scoreResult The scoreResult array as provided to onScoreQuestionReturn().
 * @return bool True if we have a correct answer. False if not.
 */
function _hasCorrectAnswer(array $scoreResult)
{
    return (
        isset($scoreResult['extra']['answer']) // This exists and is not null.
    );
}

/**
 * Determine if score results has correct answers. (for "multans")
 *
 * Note: This is different than having a correct, singular answer.
 *
 * @param array $scoreResult The scoreResult array as provided to onScoreQuestionReturn().
 * @return bool True if we have correct answers. False if not.
 */
function _hasCorrectAnswers(array $scoreResult)
{
    return (
        !empty($scoreResult['extra']['answers']) // This exists and is not empty.
    );
}

/**
 * Determine if score results has correct randomized answers.
 *
 * This is the array of answers after the question seed has been used
 * to re-arrange question answers to minimize cheating.
 *
 * @param array $scoreResult The scoreResult array as provided to onScoreQuestionReturn().
 * @return bool True if we have correct answers. False if not.
 */
function _hasRandomAnswerKeys(array $scoreResult)
{
    return (
        // This exists and is not empty.
        !empty($scoreResult['extra']['lumenlearning']['randomAnswerKeys'])
    );
}

/**
 * Cast the correct answers to an array of answers.
 *
 * @param string|array $correctAnswers A comma delimited string or an array of answers.
 * @return array An array of correct answers.
 */
function _correctAnswersAsArray($correctAnswers)
{
    if (is_array($correctAnswers)) {
        return $correctAnswers;
    } else {
        return explode(',', $correctAnswers);
    }
}
