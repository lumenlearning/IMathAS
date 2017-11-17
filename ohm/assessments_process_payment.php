<?php

require_once(__DIR__ . "/../init.php");

require_once(__DIR__ . "/includes/StudentPayment.php");
require_once(__DIR__ . "/includes/StudentPaymentApi.php");

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

$action = Sanitize::simpleString($_POST['action']);
$groupId = Sanitize::onlyInt($_POST['group_id']);
$courseId = Sanitize::courseId($_POST['course_id']);
$assessmentId = Sanitize::onlyInt($_POST['assessment_id']);

$validActions = array('activate_code', 'begin_trial', 'extend_trial');

if (!in_array($action, $validActions) || "" == trim($courseId) || "" == trim($assessmentId)) {
	header("Location: " . $GLOBALS['basesiteurl']);
	exit;
}

$assessmentUrl = $GLOBALS['basesiteurl'] . sprintf("/assessment/showtest.php?id=%d&cid=%d",
		$assessmentId, $courseId); // used by fragments/student_payment_error.php


if ("activate_code" == $action) {
	$accessCode = trim($_POST['access_code']);

	$validationError = \OHM\StudentPaymentApi::validateAccessCodeStructure($accessCode);
	if (null != $validationError) {
		$studentPayUserMessage = $validationError; // used by fragments/student_payment_error.php
		require_once(__DIR__ . "/../header.php");
		require_once(__DIR__ . "/fragments/student_payment_error.php");
		require_once(__DIR__ . "/../footer.php");
		exit;
	}

	$studentPayment = new OHM\StudentPayment($groupId, $courseId, $GLOBALS['userid']);
	$studentPayStatus = null;

	try {
		$studentPayStatus = $studentPayment->activateCode($accessCode);
	} catch (\OHM\StudentPaymentException $e) {
		// We have no global application process for catching exceptions and displaying pretty error pages.
		error_log(sprintf("Exception while attempting to activate student access code. %s -- %s",
			$e->getMessage(), $e->getTraceAsString()));
		$studentPayUserMessage = "Error while attempting to activate access code."
			. " Please check your access code or contact support."; // used by fragments/student_payment_error.php
		require_once(__DIR__ . "/../header.php");
		require_once(__DIR__ . "/fragments/student_payment_error.php");
		require_once(__DIR__ . "/../footer.php");
		exit;
	}

	if ($studentPayStatus->getStudentHasValidAccessCode()) {
		header("Location: " . $GLOBALS['assessmentUrl']);
		exit;
	} else {
		$studentPayUserMessage = "Failed to activate access code."; // used by fragments/student_payment_error.php
		require_once(__DIR__ . "/../header.php");
		require_once(__DIR__ . "/fragments/student_payment_error.php");
		require_once(__DIR__ . "/../footer.php");
		exit;
	}
}

if ("begin_trial" == $action) {
	$studentPayment = new OHM\StudentPayment($groupId, $courseId, $GLOBALS['userid']);
	$studentPayStatus = null;

	try {
		$studentPayStatus = $studentPayment->beginTrial();
	} catch (\OHM\StudentPaymentException $e) {
		// We have no global application process for catching exceptions and displaying pretty error pages.
		error_log(sprintf("Exception while attempting to begin student assessments trial. %s -- %s",
			$e->getMessage(), $e->getTraceAsString()));
		// used by fragments/student_payment_error.php
		$studentPayUserMessage = "Error while attempting to begin trial."
			. " Please check your access code or contact support.";
		require_once(__DIR__ . "/../header.php");
		require_once(__DIR__ . "/fragments/student_payment_error.php");
		require_once(__DIR__ . "/../footer.php");
		exit;
	}

	if ($studentPayStatus->getStudentIsInTrial()) {
		try {
			$studentPayment->logBeginTrial();
		} catch (\OHM\StudentPaymentException $e) {
			// We don't want to block the user due to a metrics-related failure.
			error_log("Failed to log event for student beginning an assessment trial. " . $e->getMessage());
			error_log($e->getTraceAsString());
		}
		header("Location: " . $GLOBALS['assessmentUrl']);
		exit;
	} else {
		$studentPayUserMessage = "Failed to begin trial."; // used by fragments/student_payment_error.php
		require_once(__DIR__ . "/../header.php");
		require_once(__DIR__ . "/fragments/student_payment_error.php");
		require_once(__DIR__ . "/../footer.php");
		exit;
	}
}

if ("extend_trial" == $action) {
	$studentPayment = new OHM\StudentPayment($groupId, $courseId, $GLOBALS['userid']);
	$studentPayStatus = null;

	try {
		$studentPayStatus = $studentPayment->extendTrial();
	} catch (\OHM\StudentPaymentException $e) {
		// We have no global application process for catching exceptions and displaying pretty error pages.
		error_log(sprintf("Exception while attempting to extend student assessments trial. %s -- %s",
			$e->getMessage(), $e->getTraceAsString()));
		// used by fragments/student_payment_error.php
		$studentPayUserMessage = "Error while attempting to begin trial."
			. " Please check your access code or contact support.";
		require_once(__DIR__ . "/../header.php");
		require_once(__DIR__ . "/fragments/student_payment_error.php");
		require_once(__DIR__ . "/../footer.php");
		exit;
	}

	if ($studentPayStatus->getStudentIsInTrial()) {
		header("Location: " . $GLOBALS['assessmentUrl']);
		exit;
	} else {
		$studentPayUserMessage = "Failed to begin trial."; // used by fragments/student_payment_error.php
		require_once(__DIR__ . "/../header.php");
		require_once(__DIR__ . "/fragments/student_payment_error.php");
		require_once(__DIR__ . "/../footer.php");
		exit;
	}
}




