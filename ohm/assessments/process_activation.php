<?php
/**
 * This file is only accessed on form submission when a student enters an access code,
 * begins a trial, or extends a trial for paid assessments.
 *
 * The process:
 *
 * 1. Attempt to reach the desired state via the student payment API.
 * 2. On success: Redirect the user back to the assessment.
 * 3. On failure: Display a useful message to the user with a link back to the assessment.
 */

require_once(__DIR__ . "/../../init.php");

require_once(__DIR__ . "/../includes/StudentPayment.php");
require_once(__DIR__ . "/../includes/StudentPaymentApi.php");


$action = Sanitize::simpleString($_REQUEST['action']);
$groupId = Sanitize::onlyInt($_REQUEST['group_id']);
$courseId = Sanitize::courseId($_REQUEST['course_id']);
$assessmentId = Sanitize::onlyInt($_REQUEST['assessment_id']);

$validActions = array('activate_code', 'begin_trial', 'extend_trial', 'decline_trial');

if (!in_array($action, $validActions) || "" == trim($courseId) || "" == trim($assessmentId)) {
	header("Location: " . $GLOBALS['basesiteurl']);
	exit;
}

$courseUrl = $GLOBALS['basesiteurl'] . "/course/course.php?cid=" . $courseId;
$assessmentUrl = $GLOBALS['basesiteurl'] . sprintf("/assessment/showtest.php?id=%d&cid=%d",
		$assessmentId, $courseId); // used by fragments/api_error.php


$studentPayment = new OHM\StudentPayment($groupId, $courseId, $GLOBALS['userid']);

/*
 * User is attempting to activate an access code.
 */
if ("activate_code" == $action) {
	$accessCode = trim($_POST['access_code']);

	$validationError = \OHM\StudentPaymentApi::validateAccessCodeStructure($accessCode);
	if (null != $validationError) {
		displayProcessErrorPage($validationError);
		exit;
	}

	$studentPayStatus = null;
	try {
		$studentPayStatus = $studentPayment->activateCode($accessCode);
	} catch (\OHM\StudentPaymentException $e) {
		// We have no global application process for catching exceptions and displaying pretty error pages.
		error_log(sprintf("Exception while attempting to activate student access code. %s -- %s",
			$e->getMessage(), $e->getTraceAsString()));
		displayProcessErrorPage("Error while attempting to activate access code."
			. " Please check your access code or contact support.");
		exit;
	}

	if ($studentPayStatus->getStudentHasValidAccessCode()) {
		header("Location: " . $GLOBALS['assessmentUrl']);
		exit;
	} else {
		displayProcessErrorPage("Failed to activate access code. Please check your access code or contact support.");
		exit;
	}
}

/*
 * User is attempting to begin a trial.
 */
if ("begin_trial" == $action) {
	$studentPayStatus = null;
	try {
		$studentPayStatus = $studentPayment->beginTrial();
	} catch (\OHM\StudentPaymentException $e) {
		// We have no global application process for catching exceptions and displaying pretty error pages.
		error_log(sprintf("Exception while attempting to begin student assessments trial. %s -- %s",
			$e->getMessage(), $e->getTraceAsString()));
		displayProcessErrorPage("Error while attempting to begin trial."
			. " Please check your access code or contact support.");
		exit;
	}

	if ($studentPayStatus->getStudentIsInTrial()) {
		header("Location: " . $GLOBALS['assessmentUrl']);
		exit;
	} else {
		displayProcessErrorPage("Failed to begin trial.");
		exit;
	}
}

/*
 * User is attempting to extend a trial.
 */
if ("extend_trial" == $action) {
	$studentPayStatus = null;
	try {
		$studentPayStatus = $studentPayment->extendTrial();
	} catch (\OHM\StudentPaymentException $e) {
		// We have no global application process for catching exceptions and displaying pretty error pages.
		error_log(sprintf("Exception while attempting to extend student assessments trial. %s -- %s",
			$e->getMessage(), $e->getTraceAsString()));
		displayProcessErrorPage("Error while attempting to begin trial."
			. " Please check your access code or contact support.");
		exit;
	}

	if ($studentPayStatus->getStudentIsInTrial()) {
		header("Location: " . $GLOBALS['assessmentUrl']);
		exit;
	} else {
		displayProcessErrorPage("Failed to begin trial.");
		exit;
	}
}


/**
 * Display an error page after a failure to interact with the student payments API.
 *
 * @param $message string The message to display on the error page.
 */
function displayProcessErrorPage($message)
{
	extract($GLOBALS, EXTR_SKIP | EXTR_REFS); // Sadface. :(

	$placeinhead = '<script src="' . $GLOBALS['basesiteurl'] . '/ohm/js/common/goBack.js" type="text/javascript"></script>';
	require_once(__DIR__ . "/../../header.php");

	echo '<div class="access-wrapper">';
	echo '<div class="access-block">';

	$studentPayUserMessage = $message; // Used by fragments/api_error.php
	require_once(__DIR__ . "/fragments/api_error.php");

	echo '</div><!-- end .access-block -->';
	echo '</div><!-- end .access-wrapper -->';

	require_once(__DIR__ . "/../../footer.php");

	exit;
}
