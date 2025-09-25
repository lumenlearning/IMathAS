<?php

require_once(__DIR__ . "/../../init.php");
require_once(__DIR__ . '/../assessments/payment_lib.php');

use OHM\Models\StudentPayApiResult;
use OHM\Assessments\PaymentLib;

global $studentPayStatus;

$paymentType = $studentPayStatus->getStudentPaymentTypeRequired();
$trialTimeRemaining = $studentPayStatus->getStudentTrialTimeRemainingSeconds();
$paymentStatus = $studentPayStatus->getStudentPaymentRawStatus();
$paymentAmount = $studentPayStatus->getCourseDirectPayAmountInCents();
$schoolLogoUrl = $studentPayStatus->getSchoolLogoUrl();
$stripeModalLogoUrl = 'https://s3-us-west-2.amazonaws.com/lumen-components-prod/assets/branding/LumenBlueBG-80x80.png';
$attributionLogoUrl = is_null($schoolLogoUrl) || empty($schoolLogoUrl)
	? 'null' : '\'https://s3-us-west-2.amazonaws.com/lumen-components/assets/Lumen-300x138.png\'';
$assessmentUrl = $_SERVER['REQUEST_URI'];
$endpointUrl = $GLOBALS["basesiteurl"]
	. sprintf('/ohm/assessments/activation_ajax.php?action=payment_proxy'
		. '&courseOwnerGroupId=%d&courseId=%d&studentId=%d&assessmentId=%d', $courseOwnerGroupId,
		$courseId, $userid, $assessmentId);
$apiKey = $GLOBALS["student_pay_api"]["stripe_api_key"];
$amount = "$paymentAmount"; // must be a string, and in cents (not dollars)

$activationCodeErrors = isset($_REQUEST['activationCodeErrors']) ?
	$_REQUEST['activationCodeErrors'] : null;

$stm = $DBH->prepare('SELECT email FROM imas_users WHERE id = :id');
$stm->execute(array(':id' => $userid));
$userEmail = $stm->fetch(PDO::FETCH_ASSOC)['email'];

$stm = $DBH->prepare('SELECT name FROM imas_groups WHERE id = :id');
$stm->execute(array(':id' => $courseOwnerGroupId));
$groupName = $stm->fetch(PDO::FETCH_ASSOC)['name'];

$redirectTo = null;
if ('trial_not_started' == $paymentStatus) {
	$redirectTo = $GLOBALS['basesiteurl'] . '/ohm/assessments/process_activation.php?'
		. sprintf('action=begin_trial&course_owner_group_id=%d&course_id=%d&assessment_id=%d&assessment_version=%d',
			$courseOwnerGroupId, $courseId, $assessmentId, $assessmentVersion);
}
if ('in_trial' == $paymentStatus) {
	$redirectTo = $GLOBALS['basesiteurl']
		. sprintf('/ohm/assessments/process_activation.php?action=%s&course_id=%d&assessment_id=%d&assessment_version=%d',
			'continue_trial', $courseId, $assessmentId, $assessmentVersion);
}
if ('can_extend' == $paymentStatus) {
	$redirectTo = $GLOBALS['basesiteurl'] . '/ohm/assessments/process_activation.php?'
		. sprintf('action=extend_trial&course_owner_group_id=%d&course_id=%d&assessment_id=%d&assessment_version=%d',
			$courseOwnerGroupId, $courseId, $assessmentId, $assessmentVersion);
}
if ('expired' == $paymentStatus) {
	http://ludev1.example.com/ohm/course/course.php?folder=0&cid=1
	$redirectTo = $GLOBALS['basesiteurl'] . sprintf('/ohm/course/course.php?cid=%d',
			$courseId);
}


if ('in_trial' == $paymentStatus) {
	if (PaymentLib::isStartingAssessment() || 0 > $studentPayStatus->getStudentTrialTimeRemainingSeconds()) {
		$studentPayment->logDirectPaymentPageSeen();
		displayPaymentPage();
		exit;
	}
} elseif ('paid' != $paymentStatus) {
	$studentPayment->logDirectPaymentPageSeen();
	displayPaymentPage();
	exit;
}


function displayPaymentPage()
{
	extract($GLOBALS, EXTR_SKIP | EXTR_REFS); // Sadface. :(

	require_once(__DIR__ . "/../../header.php");

	// This is used for debugging way to frequently to not have.
	printf('<!-- enrollmentid / enrollment_id = %d -->', $GLOBALS['enrollmentId']);
	?>

    <style>
        button {
            height: auto;
        }
    </style>

    <div id="paymentComponent"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/react/0.13.3/react.min.js"></script>
    <script src="<?php echo $GLOBALS['student_pay_api']['direct_pay_component_url']; ?>"></script>
    <script>
	<?php if ($GLOBALS['studentPayStatus']->getStudentIsOptedOut()) { ?>
      directPayComponents.renderMultiPayPage('paymentComponent', {
        'allowTrial': false,
    <?php } else if ($GLOBALS['paymentType'] == StudentPayApiResult::ACCESS_TYPE_DIRECT_PAY) { ?>
	  directPayComponents.renderDirectPayLandingPage('paymentComponent', {
        'allowTrial': true,
    <?php } else if ($GLOBALS['paymentType'] == StudentPayApiResult::ACCESS_TYPE_MULTI_PAY) { ?>
	  directPayComponents.renderMultiPayPage('paymentComponent', {
        'allowTrial': true,
	<?php } ?>
        'stripeKey': '<?php echo $GLOBALS['apiKey']; ?>',
        'courseTitle': '<?php echo Sanitize::encodeStringForJavascript($GLOBALS['courseName']); ?>',
        'studentName': '<?php echo Sanitize::encodeStringForJavascript($GLOBALS['userfullname']) ?>',
        'userEmail': '<?php echo Sanitize::encodeStringForJavascript($GLOBALS['userEmail']); ?>',
        'chargeAmount': '<?php echo $GLOBALS['amount']; ?>',
        'institutionOhmId': '<?php echo $GLOBALS['courseOwnerGroupId']; ?>',
        'institutionGuid': '<?php echo $GLOBALS['courseOwnerGroupGuid']; ?>',
        'institutionName': 'Lumen Learning',
        'sectionId': '<?php echo $GLOBALS['courseId']; ?>',
        'enrollmentId': '<?php echo $GLOBALS['enrollmentId']; ?>',
        'chargeDescription': '<?php echo Sanitize::encodeStringForJavascript($GLOBALS['courseName']); ?>',
        'stripeModalLogoUrl': '<?php echo $GLOBALS['stripeModalLogoUrl']; ?>',
        'endpointUrl': '<?php echo $GLOBALS['endpointUrl']; ?>',
        'redirectTo': '<?php echo $GLOBALS['redirectTo']; ?>',
        'assessmentUrl': '<?php echo $GLOBALS['assessmentUrl'] ?>',
        'schoolLogoUrl': '<?php echo $GLOBALS['schoolLogoUrl']; ?>',
        'attributionLogoUrl': <?php echo $GLOBALS['attributionLogoUrl']; ?>,
        'trialTimeRemaining': '<?php echo $GLOBALS['trialTimeRemaining']; ?>',
        'paymentStatus': '<?php echo $GLOBALS['paymentStatus']; ?>',
      <?php if (!empty($GLOBALS['activationCodeErrors'])) { ?>
        'activationCodeErrors': '<?php echo Sanitize::encodeStringForJavascript($GLOBALS['activationCodeErrors']); ?>',
      <?php } ?>
      });
    </script>

	<?php
	require_once(__DIR__ . "/../../footer.php");

	exit;
}


