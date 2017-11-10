<?php
require_once(__DIR__ . "/../models/StudentPayStatus.php");

require_once(__DIR__ . "/../../init.php");
require_once(__DIR__ . "/../../header.php");

/**
 * This file is currently included from assessment/showtest.php. (inline, near line 60)
 *
 * $courseAndStudentPaymentInfo is created in showtest.php and must contain valid data.
 */

$paymentStatus = $studentPayStatus->getStudentPaymentRawStatus();

$canEnterCode = array(\OHM\StudentPayApiResult::NOT_PAID, \OHM\StudentPayApiResult::IN_TRIAL,
	\OHM\StudentPayApiResult::CAN_EXTEND, \OHM\StudentPayApiResult::ALL_TRIALS_EXPIRED);
$notPaid = array(\OHM\StudentPayApiResult::NOT_PAID);
$inTrial = array(\OHM\StudentPayApiResult::IN_TRIAL);
$extendTrial = array(\OHM\StudentPayApiResult::CAN_EXTEND);
$trialsExpired = array(\OHM\StudentPayApiResult::ALL_TRIALS_EXPIRED);

$userDisplayName = explode(' ', $GLOBALS['userfullname'])[0];
if ('' == trim($userDisplayName)) {
    $userDisplayName = $GLOBALS['username'];
}
?>

<div class="access-wrapper">
<div class="access-block">

<?php

$validApiResponse = false;
if (in_array($paymentStatus, $notPaid)) {
	$validApiResponse = true;
	require_once(__DIR__ . "/assessments_begin_trial.php");
}
if (in_array($paymentStatus, $inTrial)) {
	$validApiResponse = true;
	require_once(__DIR__ . "/assessments_in_trial.php");
}
if (in_array($paymentStatus, $extendTrial)) {
	$validApiResponse = true;
	require_once(__DIR__ . "/assessments_extend_trial.php");
}
if (in_array($paymentStatus, $trialsExpired)) {
	$validApiResponse = true;
	require_once(__DIR__ . "/assessments_trials_expired.php");
}
if (!$validApiResponse) {
	error_log(sprintf("Unknown response from student payment API: paymentStatus='%s'", $paymentStatus));
    require_once(__DIR__ . "/student_payment_error.php");
}

require_once(__DIR__ . "/../../footer.php");
?>

</div><!-- end .access-block -->
</div><!-- end .access-wrapper -->
