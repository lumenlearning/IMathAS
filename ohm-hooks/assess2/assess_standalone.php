<?php

// This file has tests!
// See: /ohm/tests/unit/ohm-hooks/assess2/AssessmentStandaloneTest.php

/**
 * Include the correct answers and feedback for the student's answers in scoring results.
 *
 * @param array $returnData The scoring data being returned by AssessStandalone.
 * @param array $scoreResult Score results from ScoreEngine->scoreResult().
 * @return array The scoring data with correct answers included.
 */
function onScoreQuestionReturn(array $returnData, array $scoreResult): array
{
    if (!isset($scoreResult['extra'])) {
        return $returnData;
    }

    $isMultiPart = !empty($scoreResult['extra']['anstypes']) &&
        is_array($scoreResult['extra']['anstypes']);

    $correctAnswers = _getCorrectAnswersFromScoreResult($scoreResult, $isMultiPart);

    // Some or all feedback will be shuffled later.
    $returnData['feedback'] = $scoreResult['extra']['feedback'];

    // Remove OHM1 feedback, as these are unusable by Catra.
    $returnData = _removeOhm1Feedback($returnData);

    // This was the least intrusive way (to MOM) to pass randqkeys/randkeys
    // from IMathAS\assess2\questions\scorepart\ScorePart to this hook. :(
    $randomAnswerKeymaps = $GLOBALS['ohmRandomAnswerKeymaps'] ?? [];

    // We need to unset this global to prevent the issue reported in OHM-1200.
    // This is set in two places:
    // - /ohm-hooks/assess2/questions/scorepart/choices_score_part.php
    // - /ohm-hooks/assess2/questions/scorepart/multiple_answer_score_part.php
    if (isset($GLOBALS['ohmRandomAnswerKeymaps'])) unset($GLOBALS['ohmRandomAnswerKeymaps']);

    /*
     * For questions whose answers have been shuffled to minimize cheating,
     * we need to return the correct answers and feedback *after* they've
     * been rearranged using the question seed.
     */
    if (!$isMultiPart) {
        // The existence of a "randomAnswerKeymap" indicates we need to map the
        // original, unseeded correct answer keys to the shuffled, seeded answer keys.
        if (!empty($randomAnswerKeymaps[0])) {
            $shuffledCorrectAnswers = _shuffleCorrectAnswers(
                _correctAnswersAsArray($correctAnswers[0]),
                $randomAnswerKeymaps[0]
            );

            // For single part questions, we'll use a single element array for answers.
            //   - This also matches the pattern for returning scores in scoring responses.
            $returnData['correctAnswers'] = [$shuffledCorrectAnswers];

            // Shuffle feedback.
            $returnData['feedback'] = _shuffleFeedback(
                $scoreResult['lastAnswerAsGiven'][0],
                $randomAnswerKeymaps[0],
                $returnData['feedback']
            );
        } else {
            // This must be an array of correct answers. Single part questions
            // should contain a single element array with the correct answer.
            $returnData['correctAnswers'] = $correctAnswers;
        }
    } else {
        $questionPartTypes = $scoreResult['extra']['anstypes']; // This is a simple, flat array.
        $returnData['partTypes'] = $questionPartTypes; // Needed by Skeletor.
        foreach ($questionPartTypes as $partNumber => $partType) {
            // Not all multans/choices questions are shuffled, so check for
            // shuffled answers instead of checking the question type.
            if (!empty($randomAnswerKeymaps[$partNumber])) {
                $randomAnswerKeysForPart = $randomAnswerKeymaps[$partNumber];

                $shuffledCorrectAnswers = _shuffleCorrectAnswers(
                    _correctAnswersAsArray($correctAnswers[$partNumber]),
                    $randomAnswerKeysForPart
                );

                // For multi-part questions, $correctAnswers is an array of correct
                // answers, with each element matching a question part.
                //   - Example: [42, "0,2,4"] are answers for a two part multi-part question.
                $returnData['correctAnswers'][$partNumber] = $shuffledCorrectAnswers;

                // Get only feedback for this part.
                $unshuffledPartFeedback = _getFeedbackForPart($returnData['feedback'], $partNumber);
                // Remove the unshuffled feedback from the complete array of feedbacks.
                $allFeedbackWithPartRemoved = _deleteFeedbackByKeys(
                    $returnData['feedback'], array_keys($unshuffledPartFeedback)
                );
                // Shuffle feedback for this part.
                $shuffledPartFeedback = _shuffleFeedback(
                    $scoreResult['lastAnswerAsGiven'][$partNumber],
                    $randomAnswerKeysForPart,
                    $unshuffledPartFeedback
                );
                // Merge the shuffled part feedback with all other feedback.
                $returnData['feedback'] = array_merge($allFeedbackWithPartRemoved, $shuffledPartFeedback);
            } else {
                $returnData['correctAnswers'][$partNumber] = $correctAnswers[$partNumber];
            }
        }
    }

    return $returnData;
}

/*
 * "Private" functions follow; not for use outside of this PHP file.
 */

/**
 * Remove feedback generated by OHM1 macros.
 *
 * - Additionally, report their usage via the errors field in response data.
 * - OHM1 macros are identified by looking for strings where structured arrays
 *   should exist.
 *
 * This function modifies:
 * - $response['feedback']
 * - $response['errors']
 *
 * @param array $responseData The response being returned to Catra.
 * @return array An updated response to return to Catra.
 */
function _removeOhm1Feedback(array $responseData): array
{
    $questionLevelFeedback = $responseData['feedback'];
    if (is_string($questionLevelFeedback)) {
        array_push($responseData['errors'],
            "Warning: Feedback may be available but is not being returned due to the usage of OHM1 macros!");
        // This may help troubleshooting efforts.
        if (empty($questionLevelFeedback)) {
            array_push($responseData['errors'], "Warning: OHM1 feedback is an empty string.");
        } else {
            array_push($responseData['errors'], "Warning: OHM1 feedback = " . $questionLevelFeedback);
        }
        // Catra expects feedback return in OHM2 format, so we can't do anything with this string.
        $responseData['feedback'] = [];
    }

    return $responseData;
}

/**
 * Get the correct answers for all question parts from the score result
 * returned from ScoreEngine->scoreResult().
 *
 * The answers returned will be the original, unseeded answers.
 *
 * @param array $scoreResult Score results from ScoreEngine->scoreResult().
 * @param bool $isMultiPart True if this is a multi-part question. False if not.
 * @return array Correct answers for all question parts, indexed by part number.
 *               Single part question answers will be indexed at 0.
 */
function _getCorrectAnswersFromScoreResult(array $scoreResult, bool $isMultiPart): array
{
    if (!$isMultiPart) {
        $correctAnswer = !empty($scoreResult['extra']['answer']) ?
            $scoreResult['extra']['answer'] : $scoreResult['extra']['answers'];
        return [$correctAnswer];
    }

    // $answer and $answers are variables defined by question writers.
    // They can BOTH exist in the same multi-part question code, with
    // each containing correct answers.
    $correctAnswers = [];
    foreach (['answer', 'answers'] as $answerVar) {
        if (!empty($scoreResult['extra'][$answerVar])) {
            foreach ($scoreResult['extra'][$answerVar] as $partNumber => $answer) {
                $correctAnswers[$partNumber] = $answer;
            }
        }
    }

    return $correctAnswers;
}

/**
 * Shuffle the correct answers for a question part using the randomAnswerKeys
 * found in $scorePartResult data.
 *
 * This is used to get the correct answers for a question after the answer keys
 * have been shuffled.
 *
 * @param array $unseededCorrectAnswers Score results from ScoreEngine->scoreResult().
 * @param array $shuffledAnswerKeymap The mapping of unseeded answer keys to shuffled keys.
 *                                    Also known as $randqkeys and $randkeys.
 *                                      Example:
 *                                        [
 *                                            // (shuffledKey) => (originalKey)
 *                                            0 => 3,
 *                                            1 => 2,
 *                                            2 => 0,
 *                                            3 => 1
 *                                        ]
 * @return string The shuffled correct answers.
 */
function _shuffleCorrectAnswers(array $unseededCorrectAnswers, array $shuffledAnswerKeymap): string
{
    $shuffledCorrectAnswers = [];
    foreach ($shuffledAnswerKeymap as $shuffledAnswerKey => $unseededAnswerKey) {
        if (in_array($unseededAnswerKey, $unseededCorrectAnswers)) {
            array_push($shuffledCorrectAnswers, $shuffledAnswerKey);
        }
    }

    return implode(',', $shuffledCorrectAnswers);
}

/**
 * Shuffle feedback and only return feedback for student provided answers.
 *
 * @param string $studentAnswers The answer keys provided by the student.
 *                                  Example: "2|3|5"
 * @param array $shuffledAnswerKeymap The mapping of unseeded answer keys to shuffled keys.
 *                                    Also known as $randqkeys and $randkeys.
 *                                      Example:
 *                                       [
 *                                           // (shuffledKey) => (originalKey)
 *                                           0 => 3,
 *                                           1 => 2,
 *                                           2 => 0,
 *                                           3 => 1
 *                                       ]
 * @param array|null $allFeedback An array containing all feedback.
 * @return array The shuffled array of feedback.
 */
function _shuffleFeedback(string $studentAnswers, array $shuffledAnswerKeymap, ?array $allFeedback): array
{
    if (empty($allFeedback)) return [];

    $studentProvidedAnswersUnseeded = explode('|', $studentAnswers);

    /*
     * Some feedback macros do not return feedback for individual answers, even if
     * the answers have been shuffled with a seed.
     *
     * Instead, they return a single feedback string for the entire question.
     *
     * An example would be using ohm_getfeedbackbasic() in a "multans" type question.
     * In this case, even if answers were shuffled, all we can do is return feedback as-is.
     */
    $feedbackKeys = array_keys($allFeedback);
    if (!preg_match('/\-/', $feedbackKeys[0])) {
        return $allFeedback;
    }

    $shuffledFeedback = [];
    if (!empty($allFeedback)) {
        foreach ($allFeedback as $feedbackAnswerKey => $feedback) {
            [$unseededKeyName, $unseededKeyNumber] = explode('-', $feedbackAnswerKey);

            // Don't include feedback for answers the student didn't provide.
            if (!in_array($unseededKeyNumber, $studentProvidedAnswersUnseeded)) continue;

            $shuffledKeyNumber = _getShuffledKeyByUnseededKey($unseededKeyNumber, $shuffledAnswerKeymap);

            $newKey = $unseededKeyName . '-' . $shuffledKeyNumber;
            $shuffledFeedback[$newKey] = $feedback;
        }
    }

    return $shuffledFeedback;
}

/**
 * Get only the feedback for a specific multi-part question part number.
 *
 * @param array|null $allFeedback An array containing all feedback for all question parts.
 * @param int $partNumber The question part number to get feedback for.
 * @return array An array of feedback for only the specified part number.
 */
function _getFeedbackForPart(?array $allFeedback, int $partNumber): array
{
    if (empty($allFeedback)) return [];

    $feedback = [];
    foreach ($allFeedback as $key => $value) {
        // All multi-part question parts are indexed like "qn1000", "qn1001", etc.
        $wantKey = "qn" . (1000 + $partNumber);
        if (preg_match('/^' . $wantKey . '/', $key)) {
            $feedback[$key] = $value;
        }
    }
    return $feedback;
}

/**
 * Delete feedbacks by key.
 *
 * @param array|null $feedback An array of feedback.
 * @param array $keysToDelete The keys to delete.
 * @return array The array of feedback with specified keys deleted.
 */
function _deleteFeedbackByKeys(?array $feedback, array $keysToDelete): array
{
    if (empty($feedback)) return [];

    foreach ($keysToDelete as $key) {
        if (!empty($feedback[$key])) {
            unset($feedback[$key]);
        }
    }

    return $feedback;
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
    }

    $correctAnswersAsArray = explode(',', $correctAnswers);
    return $correctAnswersAsArray;
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
