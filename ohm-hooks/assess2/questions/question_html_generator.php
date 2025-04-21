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
    &$toevalqtxt, // [String] The raw, unevaluated question text used to generate $question
    &$qtextvars // [array] The question text variable names
)
{
    # Store evaluated Question Text without $answerbox
    #
    # The following "normal" logic on the evaluated Question Text is excluded from the below code:
    # - handling [AB] and [SAB] syntax for answerbox(es) and solution answer box(es)
    # - handling of sequential parts (for conditional and multipart only)
    # - adding an answerbox when no answerbox was included in the $toevalqtext
    # - adding show answer & show solution buttons
    # - adding help text and hints
    # - coloring for conditional question type answerboxes
    # - Wrapping all that in a div:
    #       - $evaledqtext = "<div class=\"question\" role=region aria-label=\"" . _('Question') . "\">\n" . filter($evaledqtext);
    #       - <aforementioned handling/adding of things>
    #       - $evaledqtext .= "\n</div>\n";
    $toevalqtxtwithoutanswerbox = preg_replace('/\$answerbox/', 'ANSWERBOX_PLACEHOLDER', $toevalqtxt);
    $evaledqtextwithoutanswerbox = $this->evalWithVarInit($toevalqtxtwithoutanswerbox, $qtextvars);

    $question->setExtraData([
        'lumenlearning' => [
            'feedback' => (isset($feedback)) ? $feedback : null,
            'json' => [
                # TODO LO-1234: Complete me with more data!
                'text' => $evaledqtextwithoutanswerbox
            ]
        ]
    ]);
};
