<?php

/*
 * These are variables expected by OHM.
 *
 * They are defined here, in the Question API, because we call parts of OHM
 * code directly which expect these variables to exist.
 */
$GLOBALS['CFG']['hooks']['assess2/questions/score_engine'] = __DIR__ . '/../../../ohm-hooks/assess2/questions/score_engine.php';
$GLOBALS['CFG']['hooks']['assess2/assess_standalone'] = __DIR__ . '/../../../ohm-hooks/assess2/assess_standalone.php';
