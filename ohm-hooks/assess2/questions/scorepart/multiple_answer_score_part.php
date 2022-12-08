<?php

/**
 * Include the correct answers in scoring results after they've been randomized.
 *
 * This method is defined this way because we need access to variables
 * created in the parent scope, without specifying those variable names in the
 * calling statement. ($randqkeys may not always be available)
 */
if (!isset($randqkeys)) $randqkeys = null;
$onGetResult = function () use (
    &$scorePartResult, // [ScorePartResult] An instance of ScorePartResult
    &$randqkeys // [?array] An array of randomized correct answer keys.
) {
    // This variable contains the mapping of original question answer keys as defined
    // in question code to the shuffled answer keys.
    // If this is not defined or is empty, there is nothing for us to do here.
    if (empty($randqkeys)) return;

    /** @var \IMathAS\assess2\questions\models\ScoreQuestionParams $scoreQuestionParams */
    $scoreQuestionParams = $this->scoreQuestionParams;

    $isMultiPart = $scoreQuestionParams->getIsMultiPartQuestion(); // always returns bool
    $partNumber = $isMultiPart ? $scoreQuestionParams->getQuestionPartNumber() : 0;

    /** @var \IMathAS\assess2\questions\models\ScorePartResult $scorePartResult */
    $scorePartResult->setExtraData([
        'lumenlearning' => [
            'randomAnswerKeys' => [
                $partNumber => $randqkeys
            ]
        ],
    ]);
};
