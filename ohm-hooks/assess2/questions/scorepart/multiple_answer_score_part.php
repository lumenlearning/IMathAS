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
    &$randqkeys // [?array] An array of randomized correct answer keys.
                //          This should be set by MultipleAnswerScorePart.
) {
    // This variable contains the mapping of original question answer keys as defined
    // in question code to the shuffled answer keys.
    // If this is not defined or is empty, there is nothing for us to do here.
    if (empty($randqkeys)) return;

    /** @var \IMathAS\assess2\questions\models\ScoreQuestionParams $scoreQuestionParams */
    $scoreQuestionParams = $this->scoreQuestionParams;

    $isMultiPart = $scoreQuestionParams->getIsMultiPartQuestion(); // always returns bool
    $partNumber = $isMultiPart ? $scoreQuestionParams->getQuestionPartNumber() : 0;

    // We need to keep this updated outside the scope of ScorePartResult, so we
    // can provide the random answer keymap for all parts of a multi-part question.
    // This information is needed by the Question API.
    // This global will be unset in: /ohm-hooks/assess2/assess_standalone.php
    $GLOBALS['ohmRandomAnswerKeymaps'][$partNumber] = $randqkeys;
};
