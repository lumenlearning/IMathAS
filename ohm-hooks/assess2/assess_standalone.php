<?php

/**
 * Include the correct answers and feedback for the student's answers in scoring results.
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
    $returnData['feedback'] = $scoreResult['extra']['feedback'];

    /*
     * For questions whose answers have been shuffled to minimize cheating,
     * we need to return the correct answers *after* they've been rearranged
     * using the question seed.
     *
     * If feedback is being returned, we need to do this for feedback as well.
     *
     * The existence of "randomAnswerKeys" indicates we need to map the original,
     * unseeded correct answer index(es) to the shuffled, seeded answer index(es).
     */

    // Handle shuffled "multans" type question answers.
    if (
        _hasCorrectAnswers($scoreResult)
        && !_hasCorrectAnswer($scoreResult)
        && _hasRandomAnswerKeys($scoreResult)
    ) {
        $unseededCorrectAnswers = _correctAnswersAsArray($scoreResult['extra']['answers']);
        $shuffledAnswerKeymap = $scoreResult['extra']['lumenlearning']['randomAnswerKeys'];

        // Reorder correct answers.
        $shuffledCorrectAnswers = [];
        foreach ($shuffledAnswerKeymap as $shuffledAnswerKey => $unseededAnswerKey) {
            if (in_array($unseededAnswerKey, $unseededCorrectAnswers)) {
                array_push($shuffledCorrectAnswers, $shuffledAnswerKey);
            }
        }

        // Replace the original, unseeded answers with the shuffled answers.
        $returnData['correctAnswers']['answers'] = implode(',', $shuffledCorrectAnswers);

        // Reorder feedback.
        // Note: This only works for single-part questions.
        // TODO: Need to handle multi-part questions containing multans parts.
        $studentProvidedAnswersUnseeded = explode('|', $scoreResult['lastAnswerAsGiven'][0]);
        $shuffledFeedback = [];
        if (!empty($scoreResult['extra']['feedback'])) {
            foreach ($scoreResult['extra']['feedback'] as $feedbackAnswerKey => $feedback) {
                [$unseededKeyName, $unseededKeyNumber] = explode('-', $feedbackAnswerKey);

                // Don't include feedback for answers the student didn't provide.
                if (!in_array($unseededKeyNumber, $studentProvidedAnswersUnseeded)) continue;

                $shuffledKeyNumber = _getShuffledKeyByUnseededKey($unseededKeyNumber, $shuffledAnswerKeymap);

                $newKey = $unseededKeyName . '-' . $shuffledKeyNumber;
                $shuffledFeedback[$newKey] = $feedback;
            }

            // Replace the original, unseeded feedback with the shuffled feedback.
            $returnData['feedback'] = $shuffledFeedback;
        }
    }

    // Handle shuffled "choices" type question answers.
    if (
        _hasCorrectAnswer($scoreResult)
        && !_hasCorrectAnswers($scoreResult)
        && _hasRandomAnswerKeys($scoreResult)
    ) {
        $unseededCorrectAnswer = (int)$scoreResult['extra']['answer'];

        $shuffledCorrectAnswer = array_search($unseededCorrectAnswer,
            $scoreResult['extra']['lumenlearning']['randomAnswerKeys']);

        // Replace the original, unseeded answers with the shuffled answers.
        $returnData['correctAnswers']['answer'] = $shuffledCorrectAnswer;

        // TODO: Do we need to reorder feedback here as well? (Most likely yes!)
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

/**
 * Get an answer or feedback key after it's been shuffled by a question seed.
 *
 * @param int $unseededKey The original answer or feedback key as it would exist in
 *                         question code ("question control"), before being shuffled
 *                         by a question seed.
 * @param array $randomAnswerKeymap The mapping of original answer/feedback keys to the
 *                                  keys after a question seed has been applied.
 *                                    Example:
 *                                      [
 *                                          // (shuffledKey) => (originalKey)
 *                                          0 => 3,
 *                                          1 => 2,
 *                                          2 => 0,
 *                                          3 => 1
 *                                      ]
 *
 * @return int|null The answer or feedback key after it's been shuffled.
 */
function _getShuffledKeyByUnseededKey(int $unseededKey, array $randomAnswerKeymap): ?int
{
    $seededKey = null;

    foreach ($randomAnswerKeymap as $key => $value) {
        if ($value == $unseededKey) {
            $seededKey = $key;
        }
    }

    return $seededKey;
}
