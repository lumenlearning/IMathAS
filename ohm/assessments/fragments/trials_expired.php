<?php
/**
 * This file is included from fragments/activation.php.
 */
?>

<h1 class="greeting"><span class="emphasis"><?php echo Sanitize::encodeStringForDisplay($userDisplayName); ?></span>, your 2 week trial access has ended.</h1>
<div class="sub-wrapper">
	<img id="hourglass-icon" src="<?php echo $GLOBALS['basesiteurl'] . '/ohm/img/hourglass.png'; ?>" alt="hourglass icon" />
	<h2 id="subhead">You need to purchase access</h2>
</div>
<p class="blurb">
  You need to enter an access code to take the rest of your OHM assessments and
  complete this course. [Bookstore instructions] Ask for:
  [course name](Waymaker Bundle) / or OHM Platform Access Code.
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
