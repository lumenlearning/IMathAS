<?php

/**
 * Include the correct answers in scoring results after they've been randomized.
 *
 * This method is defined this way because we need access to variables
 * created in the parent scope, without specifying those variable names in the
 * calling statement. ($randkeys may not always be available)
 */
if (!isset($randkeys)) $randkeys = null;
$onGetResult = function () use (
    &$scorePartResult, // [ScorePartResult] An instance of ScorePartResult
    &$randkeys // [?array] An array of randomized correct answer keys.
)
{
    if (!empty($randkeys)) {
        $scorePartResult->setExtraData([
            'lumenlearning' => [
                'randomAnswerKeys' => $randkeys
            ],
        ]);
    }
};
