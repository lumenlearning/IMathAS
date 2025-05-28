<?php

/*
 * This config is intended to be specific to the Lumen API (Lumen Laravel Framework)
 *  and should have no effect on OHM (move an OHM-related config to ohm.php)
 */

// access to Sanitize class
require_once(__DIR__ . '/../../../includes/sanitize.php');

$GLOBALS['QUESTIONS_API']['EDITABLE_QTYPES'] = explode(',', getenv('QUESTIONS_API_EDITABLE_QTYPES') ?: 'choices');
$GLOBALS['QUESTIONS_API']['EDITABLE_QTEXT_HTML_TAGS'] = explode(',', getenv('QUESTIONS_API_EDITABLE_QTEXT_HTML_TAGS') ?: 'b,i,u,strong,em,sub,sup,p,div,span,br,a,ol,ul,li,img,table,tr,th,td,caption,col,colgroup,thead,tbody,tfoot');
