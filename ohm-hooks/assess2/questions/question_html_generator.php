<?php

/**
 * Override variables declared from question code evals before generating
 * question text and answer boxes.
 *
 * We are currently using this to override answer shuffling globally in OHM.
 */

use IMathAS\assess2\questions\answerboxes\AnswerBoxOhmExtensions;

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
    &$quesData, // [?array] The question data from the generator
    &$evaledqtextwithoutanswerbox, // [String] The question text (HTML) without embedded answer boxes
                                   // If $answerbox was present, it was replaced with "ANSWERBOX_PLACEHOLDER".
    &$answerBoxGenerators // [?array] The AnswerBox generator(s)
)
{
    $extraData = [];
    $extraData['lumenlearning'] = [
        'feedback' => (isset($feedback)) ? $feedback : null
    ];

    /*
     * The following gathers data from QuestionHtmlGenerator and all AnswerBox
     * generator(s) to form the "questionComponentsByQuestionPart" section in
     * question API responses in Lumen One. (OHM 2)
     */

    $allAnswerBoxVariables = [];
    foreach ($answerBoxGenerators as $answerBoxGenerator) {
        if ($answerBoxGenerator instanceof AnswerBoxOhmExtensions) {
            $allAnswerBoxVariables = array_merge($allAnswerBoxVariables, $answerBoxGenerator->getQuestionOptionVariables());
        }
    }

    $questionComponents = [];
    $questionComponents['text'] = $evaledqtextwithoutanswerbox;
    $questionComponents['type'] = $quesData['qtype'];
    $questionComponents['feedback'] = $feedback;

    // This contains data from all AnswerBox generators, indexed by part number.
    // Single part questions will be indexed with "qn0".
    // Multi-part questions will be indexed as such: "qn1000", "qn1001", etc.
    $questionComponents['componentsByQnIdentifier'] = $allAnswerBoxVariables;

    $extraData['lumenlearning']['question_components'] = $questionComponents;

    $question->setExtraData($extraData);
};
