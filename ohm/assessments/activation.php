<?php
/**
 * This file is currently included from assessment/showtest.php. (inline, near line 550)
 *
 * $studentPayStatus is created in showtest.php and must contain valid data.
 */

require_once(__DIR__ . "/../models/StudentPayStatus.php");

// Constants representing student access code state.
$canEnterCode = array(\OHM\StudentPayApiResult::NOT_PAID, \OHM\StudentPayApiResult::IN_TRIAL,
	\OHM\StudentPayApiResult::CAN_EXTEND, \OHM\StudentPayApiResult::ALL_TRIALS_EXPIRED);
$notPaid = array(\OHM\StudentPayApiResult::NOT_PAID);
$inTrial = array(\OHM\StudentPayApiResult::IN_TRIAL);
$extendTrial = array(\OHM\StudentPayApiResult::CAN_EXTEND);
$trialsExpired = array(\OHM\StudentPayApiResult::ALL_TRIALS_EXPIRED);


// Used inside page fragments.
$userDisplayName = explode(' ', $GLOBALS['userfullname'])[0];
if ('' == trim($userDisplayName)) {
	$userDisplayName = $GLOBALS['username'];
}


$paymentStatus = $GLOBALS['studentPayStatus']->getStudentPaymentRawStatus();
$pageDisplayed = false;
$trialReminderPageDisplayed = false;

if (in_array($paymentStatus, $inTrial)) {
	if (!seenTrialReminderPage($GLOBALS['assessmentId'])) {
		setTrialReminderCookie($GLOBALS['assessmentId']);
		$pageDisplayed = displayStudentPaymentPage(__DIR__ . "/fragments/in_trial.php");
	} else {
		$trialReminderPageDisplayed = true;
	}
}
if (in_array($paymentStatus, $notPaid)) {
	setTrialReminderCookie($assessmentId);
	$pageDisplayed = displayStudentPaymentPage(__DIR__ . "/fragments/begin_trial.php");
}
if (in_array($paymentStatus, $extendTrial)) {
	setTrialReminderCookie($assessmentId);
	$pageDisplayed = displayStudentPaymentPage(__DIR__ . "/fragments/extend_trial.php");
}
if (in_array($paymentStatus, $trialsExpired)) {
	$pageDisplayed = displayStudentPaymentPage(__DIR__ . "/fragments/trials_expired.php");
}

// A page has been displayed, but not the trial reminder page. (need to add footer content)
if (!$trialReminderPageDisplayed && $pageDisplayed) {
	require_once(__DIR__ . "/../../footer.php");
	// Prevent the user from viewing the assessment.
	exit;
}


if (!$trialReminderPageDisplayed) {
	// We did not receive a recognized status from the student payment API.
	error_log(sprintf("Unknown response from student payment API: paymentStatus='%s'", $paymentStatus));
	// Business decision: On payment API errors, do nothing and let students through to assessments.
}



/**
 * Determine if the user has been reminded to pay for assessments.
 *
 * @param $assessmentId integer The assessment ID.
 * @return bool True if yes, False if no or max reminder time elapsed.
 */
function seenTrialReminderPage($assessmentId) {
	$trialLastReminderTimeCookieName = "seen_access_code_page_asid_" . $assessmentId;

	if (!isset($_COOKIE[$trialLastReminderTimeCookieName])) {
	    return false;
    }

    $timeDiff = time() - $_COOKIE[$trialLastReminderTimeCookieName];
    if ($timeDiff > $GLOBALS['student_pay_api']['trial_min_reminder_time_secs']) {
        return false;
    }

    return true;
}

/**
 * Set a session-scoped cookie to store the time a user viewed the assessments payment
 * screen for this assessment.
 *
 * @param $assessmentId integer The assessment ID.
 */
function setTrialReminderCookie($assessmentId){
	$trialLastReminderTimeCookieName = "seen_access_code_page_asid_" . $assessmentId;

    setcookie($trialLastReminderTimeCookieName, time(), 0);
}

/**
 * Display a PHP file. If the page header has not already been displayed, display that first.
 *
 * @param $phpFilename string The complete path to the PHP file.
 * @return boolean Always.
 */
function displayStudentPaymentPage($phpFilename) {
    extract($GLOBALS, EXTR_SKIP | EXTR_REFS); // Sadface. :(

	if (!$GLOBALS['pageDisplayed']) {
		$placeinhead = '<script src="' . $GLOBALS['basesiteurl'] . '/ohm/js/common/goBack.js" type="text/javascript"></script>';
		require_once(__DIR__ . "/../../header.php");
	}

    ?>
<div class="access-wrapper">
<div class="access-block">
    <?php require_once($phpFilename); ?>
</div><!-- end .access-block -->
</div><!-- end .access-wrapper -->
    <?php

    return true;
}
