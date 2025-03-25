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
    &$question, // [Question] The question object to be returned by getQuestion().
    &$feedback, // [?array] The feedback for the question.
    &$quesData, // [?array] The question data from the generator
    &$evaledqtextwithoutanswerbox, // [String] The HTML without embedded asnwer boxes
    &$answerBoxGenerators // [?array] The AnswerBox generator(s)
)
{
    $extraData = [];
    $extraData['lumenlearning'] = [
        'feedback' => (isset($feedback)) ? $feedback : null
    ];

    /*
        * Piece together relevant data for JSON representation.
        * This is only supported for choices type questions currently
    */

    if ($quesData['qtype'] == 'choices') {
        $json = [];
        $partsJson = [];

        foreach ($answerBoxGenerators as $answerBoxGenerator) {
            $partsJson[] = $answerBoxGenerator->getVariables();
        }

        $json['text'] = $evaledqtextwithoutanswerbox;
        $json['type'] = $quesData['qtype'];
        $json['feedback'] = $feedback;

        // contains each part of the question
        // (single element for non-multipart questions)
        $json['parts'] = $partsJson;

        $extraData['json'] = $json;
    }

    $question->setExtraData($extraData);
};
