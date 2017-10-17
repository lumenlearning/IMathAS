<?php

require_once(__DIR__ . "/includes/StudentPayment.php");

require_once(__DIR__ . "/../init.php");

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

$validActions = array('activate_code', 'begin_trial', 'extend_trial');

$action = Sanitize::simpleString($_POST['action']);
$groupId = Sanitize::onlyInt($_POST['group_id']);
$courseId = Sanitize::courseId($_POST['course_id']);
$assessmentId = Sanitize::onlyInt($_POST['assessment_id']);


if (!in_array($action, $validActions)) {
	header("Location: " . $GLOBALS['basesiteurl']);
	exit;
}


if ("activate_code" == $action) {
	$accessCode = Sanitize::simpleString($_POST['access_code']);

	$studentPayment = new OHM\StudentPayment($groupId, $courseId, $GLOBALS['userid']);
	$studentPayStatus = $studentPayment->activateCode($accessCode);

	if ($studentPayStatus->getStudentHasValidAccessCode()) {
		redirectToAssessment($assessmentId, $courseId);
	} else {
		// TODO: Implement me!
		require_once(__DIR__ . "/../header.php");
		echo "<p>Failed to activate the access code.</p>";
		printf("<p>%s</p>", $studentPayStatus->getUserMessage());
		require_once(__DIR__ . "/../footer.php");
		// Display a useful message to the user.
		// Provide a link back to the assessment page, which will allow the user to re-enter an access code.
	}
}

if ("begin_trial" == $action) {
	$studentPayment = new OHM\StudentPayment($groupId, $courseId, $GLOBALS['userid']);
	$studentPayStatus = $studentPayment->beginTrial();

	if ("in_trial" == $studentPayStatus->getStudentPaymentRawStatus()) {
		redirectToAssessment($assessmentId, $courseId);
	} else {
		// TODO: Implement me!
		require_once(__DIR__ . "/../header.php");
		echo "<p>Failed to begin trial.</p>";
		printf("<p>%s</p>", $studentPayStatus->getUserMessage());
		require_once(__DIR__ . "/../footer.php");
		// Display a useful message to the user.
		// Provide a link back to the assessment page.
	}
}

if ("extend_trial" == $action) {
	$studentPayment = new OHM\StudentPayment($groupId, $courseId, $GLOBALS['userid']);
	$studentPayStatus = $studentPayment->extendTrial();

	if ("in_trial" == $studentPayStatus->getStudentPaymentRawStatus()) {
		redirectToAssessment($assessmentId, $courseId);
	} else {
		// TODO: Implement me!
		require_once(__DIR__ . "/../header.php");
		echo "<p>Failed to extend trial.</p>";
		printf("<p>%s</p>", $studentPayStatus->getUserMessage());
		require_once(__DIR__ . "/../footer.php");
		// Display a useful message to the user.
		// Provide a link back to the assessment page.
	}
}


require_once(__DIR__ . "/../footer.php");


function redirectToAssessment($assessmentId, $courseId)
{
	$assessmentUrl = $GLOBALS['basesiteurl'] . sprintf("/assessment/showtest.php?id=%d&cid=%d",
			$assessmentId, $courseId);
	header("Location: " . $assessmentUrl);
	exit;
}

