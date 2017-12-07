<?php
/**
 * This file is included from fragments/assessments_payment.php.
 */
?>

<h1 class="greeting"><span class="emphasis"><?php echo $userDisplayName; ?></span>, your 2 week trial access has ended.</h1>
<div class="sub-wrapper">
	<img id="hourglass-icon" src="<?php echo $GLOBALS['basesiteurl'] . '/ohm/img/hourglass.png'; ?>" alt="hourglass icon" />
	<h2 id="subhead">You need to purchase access</h2>
</div>
<p class="blurb">
  You need to enter an access code to take the rest of your OHM assessments and
  complete this course. You can purchase one at your campus bookstore (ask for
	[access code name]) or on the bookstore website[URL].
</p>

<?php
if (in_array($paymentStatus, $canEnterCode)) {
  $validApiResponse = true;
  require_once(__DIR__ . "/assessments_activate_code.php");
}
?>

<h2 id="subhead">In a pinch?</h2>
<p>
  If you have an urgent deadline but aren’t able to purchase access yet, you can
  use a one-time 24 hour pass. You can only do this once, so be sure to purchase
  your access code before your next homework or quiz is due.
</p>

<div class="trial_button_wrapper">
  <form method="POST" action="<?php echo $GLOBALS['basesiteurl']; ?>/ohm/assessments_process_payment.php">
      <input type="hidden" name="action" value="extend_trial"/>
      <input type="hidden" name="group_id" value="<?php echo $GLOBALS['groupid']; ?>"/>
      <input type="hidden" name="course_id" value="<?php echo Sanitize::encodeStringForDisplay($_REQUEST['cid']); ?>"/>
      <input type="hidden" name="assessment_id" value="<?php echo Sanitize::encodeStringForDisplay($_REQUEST['id']); ?>"/>
      <button id="begin_trial" type="submit" value="Extend Trial">Use my free 24 hour pass</button>
  </form>
  <br/>
  <p>
    <a href="#">Exit to save my pass for later</a>
  </p>
</div>
