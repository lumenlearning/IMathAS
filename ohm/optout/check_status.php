<?php

use OHM\Services\OptOutService;

$optOutService = new OptOutService($GLOBALS['DBH']);

if ($optOutService->isOptedOutOfAssessments($GLOBALS['userid'], $GLOBALS['cid'])) {
    require_once(__DIR__ . '/../../header.php');
    require(__DIR__ . '/opted_out_message.php');
    require(__DIR__ . '/../../footer.php');
    exit;
}
