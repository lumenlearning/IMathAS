<?php
/**
 * This file is currently included from assessment/showtest.php. (inline, near line 640)
 *
 * $studentPayStatus is created in showtest.php and must contain valid data.
 */

use OHM\Includes\StudentPayment;
use OHM\Models\StudentPayApiResult;

// Constants representing student access code state.
$canEnterCode = array(StudentPayApiResult::NO_TRIAL_NO_ACTIVATION, StudentPayApiResult::IN_TRIAL,
	StudentPayApiResult::CAN_EXTEND, StudentPayApiResult::ALL_TRIALS_EXPIRED);
$notPaid = array(StudentPayApiResult::NO_TRIAL_NO_ACTIVATION);
$inTrial = array(StudentPayApiResult::IN_TRIAL);
$extendTrial = array(StudentPayApiResult::CAN_EXTEND);
$trialsExpired = array(StudentPayApiResult::ALL_TRIALS_EXPIRED);


// Used inside page fragments.
$userDisplayName = explode(' ', $GLOBALS['userfullname'])[0];
if ('' == trim($userDisplayName)) {
	$userDisplayName = $GLOBALS['username'];
}

$studentPayment = new StudentPayment($courseOwnerGroupId, $GLOBALS['cid'], $GLOBALS['userid']);

$paymentStatus = $GLOBALS['studentPayStatus']->getStudentPaymentRawStatus();
$pageDisplayed = false;
$trialPageSeen = false;

if (in_array($paymentStatus, $inTrial)) {
    if (isStartingAssessment()) {
		$studentPayment->logActivationPageSeen();
		$pageDisplayed = displayStudentPaymentPage(__DIR__ . "/fragments/in_trial.php");
	} else {
        $trialPageSeen = true;
    }
}
if (in_array($paymentStatus, $notPaid)) {
	$studentPayment->logActivationPageSeen();
	$pageDisplayed = displayStudentPaymentPage(__DIR__ . "/fragments/begin_trial.php");
}
if (in_array($paymentStatus, $extendTrial)) {
	$studentPayment->logActivationPageSeen();
	$pageDisplayed = displayStudentPaymentPage(__DIR__ . "/fragments/extend_trial.php");
}
if (in_array($paymentStatus, $trialsExpired)) {
	$studentPayment->logActivationPageSeen();
	$pageDisplayed = displayStudentPaymentPage(__DIR__ . "/fragments/trials_expired.php");
}


if ($pageDisplayed) {
	require_once(__DIR__ . "/../../footer.php");
	exit;
}

if (!$pageDisplayed && !$trialPageSeen) {
    // We did not receive a recognized status from the student payment API.
	error_log(sprintf(
		"ERROR: Reached end of decision tree in activation.php. Unable to determine student activation code status.",
		$paymentStatus));
    // Business decision: On payment API errors, do nothing and let students through to assessments.
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
