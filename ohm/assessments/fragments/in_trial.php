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
	$formattedTimeRemaining = intval(gmdate('i', $trialTimeRemaining)) . ' minutes';
}
// 1 hour left in trial
else if (3600 <= $trialTimeRemaining && 7200 > $trialTimeRemaining) {
	$formattedTimeRemaining = gmdate('G', $trialTimeRemaining) . ' hour';
}
// less than 1 day left in trial
else if (86400 > $trialTimeRemaining) {
	$formattedTimeRemaining = gmdate('G', $trialTimeRemaining) . ' hours';
}
// days remaining in trial
else if (86400 <= $trialTimeRemaining) {
	$formattedTimeRemaining = gmdate('j', $trialTimeRemaining) . ' days';
}
?>

<h1 class="greeting">You have <?php echo $formattedTimeRemaining; ?> left in your trial.</h1>

<div class="access-sub-block">
  <div class="access-sub-block-left">
    <?php
    if (in_array($paymentStatus, $canEnterCode)) {
        $validApiResponse = true;
      require_once(__DIR__ . "/activate_code.php");
    }
    ?>
  </div>
  <div class="access-sub-block-right">
    <p class="emphasis">Need an activation code?</p>
    <p>
      Purchase your Lumen OHM course activation code, sold exclusively <?php require(__DIR__ . '/code_purchase_location.php'); ?>
    </p>
    <p>
      Until then, you can continue with trial access.
    </p>
    <div class="trial_button_wrapper">
      <a href="<?php
         printf($GLOBALS['basesiteurl'] . '/ohm/assessments/process_activation.php?action=%s&course_id=%d&assessment_id=%d&assessment_version=%d',
            'continue_trial', $courseId, $assessmentId, $assessmentVersion);
      ?>">Continue to assessment</a>
    </div>
  </div>
</div>
