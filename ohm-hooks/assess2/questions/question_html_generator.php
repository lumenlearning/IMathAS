<?php

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
    &$question, // [Question] The question object to be returned by getQuestion().
    &$feedback // [?array] The feedback for the question.
)
{
    $question->setExtraData([
        'lumenlearning' => [
            'feedback' => (isset($feedback)) ? $feedback : null
        ]
    ]);
};
