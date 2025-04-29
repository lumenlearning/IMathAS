<?php

/**
 * Override variables declared from question code evals before generating
 * question text and answer boxes.
 *
 * We are currently using this to override answer shuffling globally in OHM.
 */
$onBeforeAnswerBoxGenerator = function () use (
    &$questionWriterVars // [?array] This is the array of variables packaged up by IMathAS.
) {
    if (isset($GLOBALS['CFG']['GEN']['noshuffle'])) {
        $questionWriterVars['noshuffle'] = $GLOBALS['CFG']['GEN']['noshuffle'];
    }
};

/**
 * Include additional feedback in scoring results.
 *
 * This method is defined this way because we need access to variables
 * dynamically created in the parent scope, without specifying those variable
 * names in the calling statement. The calling statement will be committed back
 * to IMathAS and most (all?) other IMathAS users may not define $feedback.
 */
if (!isset($feedback)) $feedback = null;
$onGetQuestion = function () use (
    // $this, // bound by default to any anonymous function in PHP, so long as the function is created within the class context
    &$question, // [Question] The question object to be returned by getQuestion().
    &$feedback, // [?array] The feedback for the question.
    &$evaledqtextwithoutanswerbox, // [string]
    &$quesData
)
{
    $question->setExtraData([
        'lumenlearning' => [
            'feedback' => (isset($feedback)) ? $feedback : null,
            'questionComponents' => [
                # TODO LO-1234: Complete me with more data!
                'text' => $evaledqtextwithoutanswerbox,
            ]
        ]
    ]);
};
