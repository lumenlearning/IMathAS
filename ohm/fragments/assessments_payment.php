<?php
require_once(__DIR__ . "/../models/StudentPayStatus.php");

require_once(__DIR__ . "/../../init.php");
require_once(__DIR__ . "/../../header.php");

/**
 * This file is currently included from assessment/showtest.php. (inline, near line 60)
 *
 * $courseAndStudentPaymentInfo is created in showtest.php and must contain valid data.
 */

$paymentStatus = $courseAndStudentPaymentInfo->getStudentPaymentRawStatus();

$canEnterCode = array(\OHM\StudentPayStatus::NOT_PAID, \OHM\StudentPayStatus::IN_TRIAL,
	\OHM\StudentPayStatus::CAN_EXTEND, \OHM\StudentPayStatus::ALL_TRIALS_EXPIRED);
$canBeginTrial = array(\OHM\StudentPayStatus::NOT_PAID);
$canExtendTrial = array(\OHM\StudentPayStatus::CAN_EXTEND);
?>


<p>Hello, <?php echo $GLOBALS['username']; ?>!</p>
<p>No access code was found for your account.</p>
<br/>


<?php

$validApiResponse = false;
if (in_array($paymentStatus, $canEnterCode)) {
    $validApiResponse = true;
	require_once(__DIR__ . "/assessments_activate_code.php");
}
if (in_array($paymentStatus, $canBeginTrial)) {
	$validApiResponse = true;
	require_once(__DIR__ . "/assessments_begin_trial.php");
}
if (in_array($paymentStatus, $canExtendTrial)) {
	$validApiResponse = true;
	require_once(__DIR__ . "/assessments_extend_trial.php");
}
if (!$validApiResponse) {
	error_log(sprintf("Unknown response from student payment API: paymentStatus='%s'", $paymentStatus));
    require_once(__DIR__ . "/payment_api_error.php");
}

require_once(__DIR__ . "/../../footer.php");
?>

