<?php
/**
 * This file is included from fragments/activation.php.
 */

$trialTimeRemaining = $studentPayStatus->getStudentTrialTimeRemainingSeconds();

// less than 1 minute left in trial
if (60 > $trialTimeRemaining) {
	$formattedTimeRemaining = 'less than 1 minute';
}
// less than 1 hour left in trial
else if (3600 > $trialTimeRemaining) {
	$formattedTimeRemaining = gmdate('i', $trialTimeRemaining) . ' minutes';
}
// 1 hour left in trial
else if (3600 <= $trialTimeRemaining && 7200 > $trialTimeRemaining) {
	$formattedTimeRemaining = gmdate('H', $trialTimeRemaining) . ' hour';
}
// less than 1 day left in trial
else if (86400 > $trialTimeRemaining) {
	$formattedTimeRemaining = gmdate('H', $trialTimeRemaining) . ' hours';
}
// days remaining in trial
else if (86400 <= $trialTimeRemaining) {
	$formattedTimeRemaining = gmdate('d', $trialTimeRemaining) . ' days';
}
?>

<h1 class="greeting"><span class="emphasis"><?php echo Sanitize::encodeStringForDisplay($userDisplayName); ?></span>,
    you have <span class="emphasis"><?php echo $formattedTimeRemaining; ?></span> left in your trial.</h1>
<div class="sub-wrapper">
	<img id="hourglass-icon" src="<?php echo $GLOBALS['basesiteurl'] . '/ohm/img/hourglass.png'; ?>" alt="hourglass icon" />
	<h2 id="subhead">Don’t forget to purchase a course activation code!</h2>
</div>
<p class="blurb">
    Once your trial has ended you will still be able to view your course materials,
    but you will need this code to complete your Lumen OHM assessments.
</p>
<p class="blurb last">
    Purchase a Lumen OHM course activation code
    <?php require(__DIR__ . '/code_purchase_location.php'); ?>
</p>

<?php
if (in_array($paymentStatus, $canEnterCode)) {
    $validApiResponse = true;
  require_once(__DIR__ . "/activate_code.php");
}
?>

<div class="trial_button_wrapper">
  <p>
    <a href="<?php echo $GLOBALS['basesiteurl'] . '/assessment/showtest.php?activation_event=continue_trial'; ?>">Continue to assessment</a>
  </p>
</div>
