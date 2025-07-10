<?php

require_once __DIR__ . '/../../ohm/lumenapi/app/Services/Interfaces/QuestionCodeParserServiceInterface.php';
require_once __DIR__ . '/../../ohm/lumenapi/app/Services/ohm/BaseService.php';
require_once __DIR__ . '/../../ohm/lumenapi/app/Services/ohm/QuestionCodeParserService.php';

use App\Services\ohm\QuestionCodeParserService;

/**
 * Called after a question is saved to the database.
 * Determines if the question is algorithmic and updates the isrand column accordingly.
 *
 * @param string $questionCode The question code
 */
$onQuestionSave = function($questionId, $questionCode, $db = null)
{
    if (!isset($db)) {
        $db = $GLOBALS['DBH'];
    }
    // Use QuestionCodeParserService to determine if the question is algorithmic
    $parser = new QuestionCodeParserService($questionCode);
    $isAlgorithmic = $parser->isAlgorithmic();

    // Update the isrand column in the database
    $isrand = $isAlgorithmic ? 1 : 0;
    $stm = $db->prepare("UPDATE imas_questionset SET isrand = :isrand WHERE id = :id");
    $stm->execute(array(':isrand' => $isrand, ':id' => $questionId));
};
