<?php
/**
 * This file is currently included from assessment/showtest.php. (inline, near line 60)
 *
 * $studentPayStatus is created in showtest.php and must contain valid data.
 */

require_once(__DIR__ . "/../models/StudentPayStatus.php");


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


$paymentStatus = $studentPayStatus->getStudentPaymentRawStatus();
$fragmentDisplayed = false;
if (in_array($paymentStatus, $notPaid)) {
	displayStudentPaymentPage(__DIR__ . "/fragments/begin_trial.php");
	$fragmentDisplayed = true;
}
if (in_array($paymentStatus, $inTrial)) {
	displayStudentPaymentPage(__DIR__ . "/fragments/in_trial.php");
	$fragmentDisplayed = true;
}
if (in_array($paymentStatus, $extendTrial)) {
	displayStudentPaymentPage(__DIR__ . "/fragments/extend_trial.php");
	$fragmentDisplayed = true;
}
if (in_array($paymentStatus, $trialsExpired)) {
	displayStudentPaymentPage(__DIR__ . "/fragments/trials_expired.php");
	$fragmentDisplayed = true;
}

if ($fragmentDisplayed) {
	require_once(__DIR__ . "/../../footer.php");
	exit;
} else {
	// We did not receive a recognized status from the student payment API.
	error_log(sprintf("Unknown response from student payment API: paymentStatus='%s'", $paymentStatus));
	// Business decision: On payment API errors, do nothing and let students through to assessments.
}


function displayStudentPaymentPage($phpFilename) {
    extract($GLOBALS, EXTR_SKIP | EXTR_REFS); // Sadface. :(

	if (!$fragmentDisplayed) {
		$placeinhead = '<script type="text/javascript">function goBack(){window.history.back();}</script>';
		require_once(__DIR__ . "/../../header.php");
	}

    ?>
<div class="access-wrapper">
<div class="access-block">
    <?php require_once($phpFilename); ?>
</div><!-- end .access-block -->
</div><!-- end .access-wrapper -->
    <?php
}

