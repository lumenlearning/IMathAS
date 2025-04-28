<?php

/*
 * These are variables expected by OHM.
 *
 * They are defined here, in the Question API, because we call parts of OHM
 * code directly which expect these variables to exist.
 */

// Use version 2 of OHM's question and scoring code.
$GLOBALS['assessUIver'] = 2;

// Override question answer shuffling.
// This setting also exists in OHM's root /config/ohm.php file.
if (getenv('NOSHUFFLE_ANSWERS')) {
    $GLOBALS['CFG']['GEN']['noshuffle'] = getenv('NOSHUFFLE_ANSWERS');
}

// Questions API (QAPI) Config does not affect regular OHM functionality
$GLOBALS['CFG']['QAPI']['editableQtypes'] = getenv('QAPI_EDITABLE_QTYPES') ?: ['choices'];
$GLOBALS['CFG']['QAPI']['editableQtextHtmlTags'] = getenv('QAPI_EDITABLE_QTEXT_HTML_TAGS') ?: ['p'];

// Define OHM hooks.
$GLOBALS['CFG']['hooks']['assess2/questions/score_engine'] =
    __DIR__ . '/../../../ohm-hooks/assess2/questions/score_engine.php';

$GLOBALS['CFG']['hooks']['assess2/assess_standalone'] =
    __DIR__ . '/../../../ohm-hooks/assess2/assess_standalone.php';

$GLOBALS['CFG']['hooks']['assess2/questions/question_html_generator'] =
    __DIR__ . '/../../../ohm-hooks/assess2/questions/question_html_generator.php';

$GLOBALS['CFG']['hooks']['assess2/questions/scorepart/multiple_answer_score_part'] =
    __DIR__ . '/../../../ohm-hooks/assess2/questions/scorepart/multiple_answer_score_part.php';

$GLOBALS['CFG']['hooks']['assess2/questions/scorepart/choices_score_part'] =
    __DIR__ . '/../../../ohm-hooks/assess2/questions/scorepart/choices_score_part.php';
