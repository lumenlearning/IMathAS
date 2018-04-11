<?php
/**
 * This file is responsible for activating student payment codes.
 *
 * It is expected consumers of this file will be AJAX clients.
 * All responses are in JSON format.
 */

namespace OHM;

require_once(__DIR__ . '/../../init.php');
require_once(__DIR__ . "/../includes/StudentPayment.php");
require_once(__DIR__ . "/../includes/StudentPaymentApi.php");
require_once(__DIR__ . "/../exceptions/StudentPaymentException.php");

$validActions = array('activate_code', 'payment_proxy');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : NULL;

// Check if we have valid action
if (!in_array($action, $validActions)) {
	response(400, 'No valid activation action specified.');
}

/**
 * This is called when a student enters an activation code when attempting
 * to access an assessment that requires it.
 */
if ("activate_code" == $action) {
	$activationCode = isset($_REQUEST['activationCode']) ? $_REQUEST['activationCode'] : NULL;
	$groupId = isset($_REQUEST['groupId']) ? $_REQUEST['groupId'] : NULL;
	$courseId = isset($_REQUEST['courseId']) ? $_REQUEST['courseId'] : NULL;
	$studentId = isset($_REQUEST['studentId']) ? $_REQUEST['studentId'] : NULL;

	$studentPayment = new StudentPayment($groupId, $courseId, $studentId);

	$studentPaymentStatus = null;
	try {
		$studentPaymentStatus = $studentPayment->activateCode($activationCode);
	} catch (StudentPaymentException $e) {
		error_log(sprintf("Exception while attempting to activate code \"%s\" for student ID %d. %s",
			$activationCode, $studentId, $e->getMessage()));
		error_log($e->getTraceAsString());
		response(503, 'Activation code service exception. Temporarily allowing access.');
	}

	if (is_null($studentPaymentStatus)) {
		error_log("Something went wrong while attempting to activate a code for assessment."
			. " Allowing the user through to assessment anyway.");
		response(503, 'Activation code service exception. Temporarily allowing access.');
	}

	if (!is_null($studentPaymentStatus) && !$studentPaymentStatus->getStudentHasValidAccessCode()) {
		if ($studentPaymentStatus->getUserMessage()) {
			response(400, $studentPaymentStatus->getUserMessage());
		}
	}

	response(200, $studentPaymentStatus->getUserMessage());

	exit;
}

/**
 * This is called by the Stripe / direct pay component upon successful payment.
 */
if ("payment_proxy" == $action) {
	$groupId = isset($_REQUEST['groupId']) ? $_REQUEST['groupId'] : NULL;
	$courseId = isset($_REQUEST['courseId']) ? $_REQUEST['courseId'] : NULL;
	$studentId = isset($_REQUEST['studentId']) ? $_REQUEST['studentId'] : NULL;

	studentPaymentDebug('Received POST data from Stripe checkout: '
		. print_r($_POST, true));
	studentPaymentDebug(sprintf(
		'Relaying Stripe data to Lumenistration. groupId=%d, courseId=%d, studentId=%d',
		$groupId, $courseId, $studentId));

	$studentPaymentApi = new StudentPaymentApi($groupId, $courseId, $studentId);
	try {
		$formData = $_POST;
		$studentPaymentApi->paymentProxy($formData);
	} catch (StudentPaymentException $e) {
		error_log(sprintf("Exception while attempting to proxy Stripe data to Lumenistration."
			. " groupId=%d, courseId=%d, studentId=%d, error: %s",
			$groupId, $courseId, $studentId, $e->getMessage()));
		error_log($e->getTraceAsString());
		header('Location: ' . $GLOBALS["basesiteurl"] . '/assessment/showtest.php', true);
		exit;
	}

	redirect_to_payment_confirmation($courseId);
	exit;
}

// If we get here, something went wrong. Send error response.
error_log("Reached end of activation_ajax.php without doing anything!"
	. "Allowing user through to assessment anyway.");
response(200, 'Unknown code activation error. Temporarily allowing access.');

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
 * Redirect a user to the direct payment confirmation page.
 *
 * @param integer $courseId The course ID for this payment confirmation.
 */
function redirect_to_payment_confirmation($courseId)
{
	$cookieData = array(
		'confirmationNum' => $_POST['chargeId'],
		'courseId' => $courseId
	);
	setcookie('ohm_payment_confirmation', json_encode($cookieData), 0);

	$confirmationUrl = $GLOBALS["basesiteurl"] . '/ohm/assessments/payment_confirmation.php';
	header('Location: ' . $confirmationUrl, true);

	exit;
}

/**
 * Log a debugging message, if debugging for student payments is enabled.
 * @param $message string The debug message to log.
 */
function studentPaymentDebug($message)
{
	if ($GLOBALS['student_pay_api']['debug']) {
		error_log($message);
	}
}

