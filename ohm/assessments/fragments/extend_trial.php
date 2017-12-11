<?php
/**
 * This file is included from fragments/activation.php.
 */
?>

<h1 class="greeting"><span class="emphasis"><?php echo Sanitize::encodeStringForDisplay($userDisplayName); ?></span>, it’s time to enter your Lumen OHM activation code.</h1>
<div class="sub-wrapper">
	<img id="hourglass-icon" src="<?php echo $GLOBALS['basesiteurl'] . '/ohm/img/hourglass.png'; ?>" alt="hourglass icon" />
	<h2 id="subhead">Your Lumen OHM trial has ended.</h2>
</div>
<p class="blurb">
	You need to enter an activation code to complete the Lumen OHM assessments in
	this course. In the meantime, you can still view your other course materials.
</p>
<p class="blurb">
    Purchase a Lumen OHM course activation code
	<?php require(__DIR__ . '/code_purchase_location.php'); ?>
</p>

<?php
if (in_array($paymentStatus, $canEnterCode)) {
  $validApiResponse = true;
  require_once(__DIR__ . "/activate_code.php");
}
?>

<h2 id="subhead">In a pinch?</h2>
<p>
  If you have an urgent deadline but don’t have an activation code yet we’ll
	extend your trial for another 24 hours. You only get one trial extension
	though, so be sure to get an activation code before your next assessment is
	due.
</p>

<div class="trial_button_wrapper">
  <form method="POST" action="<?php echo $GLOBALS['basesiteurl']; ?>/ohm/assessments/process_activation.php">
      <input type="hidden" name="action" value="extend_trial"/>
      <input type="hidden" name="group_id" value="<?php echo $GLOBALS['groupid']; ?>"/>
      <input type="hidden" name="course_id" value="<?php echo Sanitize::courseId($courseId); ?>"/>
      <input type="hidden" name="assessment_id" value="<?php echo Sanitize::onlyInt($assessmentId); ?>"/>
      <button id="begin_trial" type="submit" value="Extend Trial">Use my free 24 hour pass</button>
  </form>
  <br/>
  <a onClick="goBack()">Exit to save my pass for later</a>
</div>
