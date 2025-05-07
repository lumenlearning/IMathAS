<?php

/*
 * This config is intended to be specific to the Lumen API (Lumen Laravel Framework)
 *  and should have no effect on OHM (move an OHM-related config to ohm.php)
 */
$GLOBALS['QUESTIONS_API']['EDITABLE_QTYPES'] = getenv('QUESTIONS_API_EDITABLE_QTYPES') ?: ['choices'];
$GLOBALS['QUESTIONS_API']['EDITABLE_QTEXT_HTML_TAGS'] = getenv('QUESTIONS_API_EDITABLE_QTEXT_HTML_TAGS') ?: ['p', 'br'];
