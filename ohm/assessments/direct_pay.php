<?php
require_once(__DIR__ . "/../../init.php");
require_once(__DIR__ . "/../../header.php");

global $trialTimeRemaining;

$endpointUrl = $GLOBALS["basesiteurl"]
    . sprintf('/ohm/assessments/activation_ajax.php?action=payment_proxy'
    . '&groupId=%d&courseId=%d&studentId=%d', $courseOwnerGroupId, $courseId, $userid);
$apiKey = $GLOBALS["student_pay_api"]["stripe_api_key"];
// FIXME: How are we pricing things? Where is this value coming from??
$amount = '3000'; // must be a string
$redirectTo = $GLOBALS["basesiteurl"] . '/ohm/assessment/showtest.php';

$stm = $DBH->prepare('SELECT email FROM imas_users WHERE id = :id');
$stm->execute(array(':id' => $userid));
$userEmail = $stm->fetch(PDO::FETCH_ASSOC)['email'];

$stm = $DBH->prepare('SELECT name FROM imas_groups WHERE id = :id');
$stm->execute(array(':id' => $courseOwnerGroupId));
$groupName = $stm->fetch(PDO::FETCH_ASSOC)['name'];

$redirectTo = null;
if ('trial_not_started' == $paymentStatus) {
	$redirectTo = $GLOBALS['basesiteurl'] . '/ohm/assessments/process_activation.php?'
		. sprintf('action=begin_trial&group_id=%d&course_id=%d&assessment_id=%d',
			$courseOwnerGroupId, $courseId, $assessmentId);
}
if ('in_trial' == $paymentStatus) {
	$redirectTo = $GLOBALS['basesiteurl']
		. sprintf('/ohm/assessments/process_activation.php?action=%s&course_id=%d&assessment_id=%d',
			'continue_trial', $courseId, $assessmentId);
}
if ('can_extend' == $paymentStatus) {
	$redirectTo = $GLOBALS['basesiteurl'] . '/ohm/assessments/process_activation.php?'
		. sprintf('action=extend_trial&group_id=%d&course_id=%d&assessment_id=%d',
			$courseOwnerGroupId, $courseId, $assessmentId);
}
if ('expired' == $paymentStatus) {
	http://ludev1.example.com/ohm/course/course.php?folder=0&cid=1
	$redirectTo = $GLOBALS['basesiteurl'] . sprintf('/ohm/course/course.php?cid=%d',
			$courseId);
}

?>

<div id="directPay"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/react/0.13.3/react.min.js"></script>
<script src="<?php echo $GLOBALS['student_pay_api']['direct_pay_component_url']; ?>"></script>
<script>
  directPayComponents.renderDirectPayLandingPage('directPay', {
    'stripeKey': '<?php echo $apiKey; ?>',
    'courseTitle': '<?php echo $courseName; ?>',
    'userEmail': '<?php echo $userEmail; ?>',
    'chargeAmount': '<?php echo $amount; ?>',
    'institutionName': '<?php echo $groupName; ?>',
    'chargeDescription': '<?php echo 'Lumen OHM - ' . $courseName ?>', // FIXME: Confirm with Julie or Kate
    'stripeModalLogoUrl': null, // FIXME: Pass Stripe modal logo URL from Lumenistration
    'endpointUrl': '<?php echo $endpointUrl; ?>',
    'redirectTo': '<?php echo $redirectTo; ?>',
    'schoolLogoUrl': null, // FIXME: Pass school image URL from Lumenistration
    'lumenLogoUrl': null, // FIXME: Pass Lumen image URL from Lumenistration
    'trialTimeRemaining': '<?php echo $trialTimeRemaining; ?>',
    'paymentStatus': '<?php echo $paymentStatus; ?>',
  });
</script>

<?php
require_once(__DIR__ . "/../../footer.php");
exit;

