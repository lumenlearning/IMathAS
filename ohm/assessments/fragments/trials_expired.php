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
	this course. In the meantime, you can still view your textbook and other
	course materials.
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

<div class="trial_button_wrapper">
  <p>
    <a onClick="goBack()">
			Go Back
		</a>
  </p>
</div>
