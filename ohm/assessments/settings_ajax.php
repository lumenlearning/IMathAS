<?php
/**
 * This file is responsible for changing settings related to student payments.
 *
 * It is expected consumers of this file will be AJAX clients.
 * All responses are in JSON format.
 */

namespace OHM;

require_once(__DIR__ . '/../../init.php');
require_once(__DIR__ . "/../includes/StudentPaymentDb.php");
require_once(__DIR__ . "/../exceptions/StudentPaymentException.php");

$validActions = array('setGroupPaymentStatus');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : NULL;

// Check if we have valid action
if (!in_array($action, $validActions)) {
	response(400, 'No valid action specified.');
}

if ("setGroupPaymentStatus" == $action) {
	$paymentStatus = isset($_REQUEST['paymentStatus']) ? $_REQUEST['paymentStatus'] : NULL;
	$paymentStatus = parseBoolean($paymentStatus);
	$groupId = isset($_REQUEST['groupId']) ? $_REQUEST['groupId'] : NULL;

	if (is_null($groupId) || 1 > $groupId) {
		response(400, "Invalid group ID provided: " . $_REQUEST['groupId']);
	}
	if (is_null($paymentStatus)) {
		response(400, "Invalid payment status provided: " . $_REQUEST['paymentStatus']);
	}

	try {
		$studentPaymebtDb = new StudentPaymentDb($groupId, null, null);
		$studentPaymebtDb->setStudentPaymentAllCoursesByGroupId($groupId, $paymentStatus);
		$studentPaymebtDb->setGroupRequiresStudentPayment($paymentStatus);
	} catch (\PDOException $e) {
		error_log(sprintf("Failed to change student payment setting on group ID %d. Exception: %s",
			$groupId, $e->getMessage()));
		error_log($e->getTraceAsString());
		response(500, 'Failed to change student payment setting on group ID ' . $groupId);
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
function response($status, $msg)
{
	header('Content-Type: application/json');
	http_response_code($status);

	echo json_encode(array(
		'message' => $msg
	));

	exit;
}

/**
 * Convert a boolean string to an actual boolean.
 *
 * Because: https://secure.php.net/manual/en/language.types.boolean.php
 *
 * @param $value string A string containing either "true" or "false".
 * @return bool|null True, false, or null. Null if the string is neither true nor false.
 */
function parseBoolean($value)
{
	if ("false" == strtolower($value) || false == $value) {
		return false;
	}
	if ("true" == strtolower($value) || true == $value) {
		return true;
	}

	return null;
}
