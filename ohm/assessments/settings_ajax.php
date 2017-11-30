<?php

namespace OHM;

require_once(__DIR__ . '/../../init.php');
require_once(__DIR__ . "/../includes/StudentPaymentDb.php");

$validActions = array('setGroupPaymentStatus');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : NULL;

// Check if we have valid action
if (!in_array($action, $validActions)) {
	response(400, 'No valid action specified.');
}

if ("setGroupPaymentStatus" == $action) {
	$groupId = isset($_REQUEST['groupId']) ? $_REQUEST['groupId'] : NULL;
	$paymentStatus = isset($_REQUEST['paymentStatus']) ? $_REQUEST['paymentStatus'] : NULL;

	if (NULL == $groupId || NULL == $paymentStatus) {
		response(400, 'Missing data.');
	}

	response(200, 'OK');

	exit;
}

// If we get here, something went wrong. Send error response.
response(400, 'Something went wrong.');

/**
 * Return a response to the client.
 *
 * @param $status integer The HTTP status to return.
 * @param $msg string The human-readable message to return.
 */
function response($status, $msg) {
	header('Content-Type: application/json');
	http_response_code($status);

	echo json_encode(array(
		'message' => $msg
	));

	exit;
}
