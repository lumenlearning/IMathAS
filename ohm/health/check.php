<?php

require(__DIR__ . "/../../init_without_validate.php");

use OHM\health\HealthCheckController;


authenticate_request();
ini_set("max_execution_time", "20"); // 20 seconds


$healthCheckController = new HealthCheckController($GLOBALS['DBH']);

$item = preg_replace('/\W/', '', $_REQUEST['item']);
switch ($item) {
    case "grade_passback_queue_size":
        $healthCheckController->check_grade_passback_queue_size();
        exit;
    default:
        $healthCheckController->no_check_requested();
        exit;
}


/**
 * Modeled after processltiqueue.php's request authentication.
 * @return void
 */
function authenticate_request(): void
{
    if (!isset($GLOBALS['health_check']['auth_code'])) {
        http_response_code(401);
        echo json_encode([
            'errors' => ['Health check auth code is not configured.'],
        ]);
        exit;
    }

    if (!isset($_REQUEST['authcode']) || $GLOBALS['health_check']['auth_code'] != $_REQUEST['authcode']) {
        http_response_code(401);
        echo json_encode([
            'errors' => ['No auth code or invalid auth code provided.'],
        ]);
        exit;
    }
}
