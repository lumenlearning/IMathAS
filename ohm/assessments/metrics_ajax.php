<?php
/**
 * Record metrics for user actions related to student payments for assessments.
 *
 * This page returns a status 200 with no content.
 *
 * Failure to interact with the student payment API will not return an error to the client.
 */

require_once(__DIR__ . "/../../init.php");

require_once(__DIR__ . "/../includes/StudentPayment.php");
require_once(__DIR__ . "/../includes/StudentPaymentApi.php");


$action = Sanitize::simpleString($_REQUEST['action']);
$groupId = Sanitize::onlyInt($_REQUEST['group_id']);
$courseId = Sanitize::courseId($_REQUEST['course_id']);
$assessmentId = Sanitize::onlyInt($_REQUEST['assessment_id']);

$validActions = array('decline_trial');

if (!in_array($action, $validActions) || "" == trim($courseId) || "" == trim($assessmentId)) {
	header("Location: " . $GLOBALS['basesiteurl']);
	exit;
}


$studentPayment = new OHM\StudentPayment($groupId, $courseId, $GLOBALS['userid']);


/*
 * User has declined starting a trial.
 */
if ("decline_trial" == $action) {
	try {
		$studentPayment->logDeclineTrial();
	} catch (\OHM\StudentPaymentException $e) {
		error_log("Failed to log metric: Student declined an assessment trial. " . $e->getMessage());
		error_log($e->getTraceAsString());
	}

	exit;
}

