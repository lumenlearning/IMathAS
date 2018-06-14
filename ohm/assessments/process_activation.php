<?php
/**
 * This file is only accessed on form submission when a student enters an access code,
 * begins a trial, extends a trial for paid assessments, or starts an assessment.
 *
 * The process:
 *
 * 1. Attempt to reach the desired state via the student payment API.
 * 2. On success: Redirect the user back to the assessment.
 * 3. On failures:
 *        Known failure cases: Return a useful message suitable for display to the user in JSON format.
 *        Unknown failure cases: Allow the user to proceed to assessments.
 *
 */

require_once(__DIR__ . "/../../init.php");

use OHM\Includes\StudentPayment;
use OHM\Exceptions\StudentPaymentException;


$action = Sanitize::simpleString($_REQUEST['action']);
$groupId = Sanitize::onlyInt($_REQUEST['group_id']);
$courseId = Sanitize::courseId($_REQUEST['course_id']);
$assessmentId = Sanitize::onlyInt($_REQUEST['assessment_id']);

$validActions = array('begin_trial', 'extend_trial', 'continue_trial', 'decline_trial');

if (!in_array($action, $validActions) || "" == trim($courseId) || "" == trim($assessmentId)) {
	header("Location: " . $GLOBALS['basesiteurl']);
	exit;
}

$courseUrl = $GLOBALS['basesiteurl'] . "/course/course.php?cid=" . $courseId;

$assessmentUrl = $GLOBALS['basesiteurl'] . sprintf("/assessment/showtest.php?id=%d&cid=%d",
		$assessmentId, $courseId); // used by fragments/api_error.php

$studentPayment = new StudentPayment($groupId, $courseId, $GLOBALS['userid']);


/*
 * User is attempting to begin a trial.
 */
if ("begin_trial" == $action) {
	$studentPayStatus = null;
	try {
		$studentPayStatus = $studentPayment->beginTrial();
	} catch (StudentPaymentException $e) {
		// All unknown / uncaught errors should allow the user through to assessments.
		error_log(sprintf("Exception while attempting to begin student assessments trial. %s -- %s",
			$e->getMessage(), $e->getTraceAsString()));
	}

	if ($studentPayStatus->getStudentIsInTrial()) {
		setcookie("activation_event", "begin_trial", 0, '/');
		header("Location: " . $assessmentUrl);
		exit;
	} else {
		if ($studentPayStatus->getUserMessage()) {
			response(500, $studentPayStatus->getUserMessage());
		}
		// Failed to begin trial.
		// All unknown / unexpected errors should allow the user through to assessments.
		error_log("An unexpected error occurred in process_activation.php->begin_trial."
			. "This should be fixed. Allowing the student through to assessments anyway.");
	}
}

/*
 * User is attempting to extend a trial.
 */
if ("extend_trial" == $action) {
	$studentPayStatus = null;
	try {
		$studentPayStatus = $studentPayment->extendTrial();
	} catch (StudentPaymentException $e) {
		// All unknown / uncaught errors should allow the user through to assessments.
		error_log(sprintf("Exception while attempting to extend student assessments trial. %s -- %s",
			$e->getMessage(), $e->getTraceAsString()));
	}

	if ($studentPayStatus->getStudentIsInTrial()) {
		setcookie("activation_event", "extend_trial", 0, '/');
		header("Location: " . $assessmentUrl);
		exit;
	} else {
		if ($studentPayStatus->getUserMessage()) {
			response(500, $studentPayStatus->getUserMessage());
		}
		// Failed to extend trial.
		// All unknown / unexpected errors should allow the user through to assessments.
		error_log("An unexpected error occurred in process_activation.php->extend_trial."
			. "This should be fixed. Allowing the student through to assessments anyway.");
	}
}

/*
 * User is starting or returning to an assessment during a trial.
 */
if ("continue_trial" == $action) {
	setcookie("activation_event", "continue_trial", 0, '/');
	header("Location: " . $assessmentUrl);
	exit;
}


/**
 * Return a response to the client.
 *
 * @param $status integer The HTTP status to return.
 * @param $msg string The human-readable message to return.
 */
function response($status, $msg)
{
	http_response_code($status);
	header('Content-Type: application/json');

	echo json_encode(array(
		'message' => $msg
	));

	exit;
}


// All unknown / unexpected errors should allow the user through to assessments.
error_log("An unexpected error occurred in process_activation.php. This needs to be fixed."
	. "Allowing the student through to assessments anyway.");

