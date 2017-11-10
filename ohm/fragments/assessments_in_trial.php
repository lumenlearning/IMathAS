<?php
/**
 * This file is included from fragments/assessments_payment.php.
 */
?>

<h1 class="greeting"><span class="emphasis"><?php echo $userDisplayName; ?></span>, you have <span class="emphasis"># of days</span> remaining in your trial!</h1>
<div class="sub-wrapper">
	<img id="hourglass-icon" src="<?php echo $GLOBALS['basesiteurl'] . '/ohm/img/hourglass.png'; ?>" alt="hourglass icon" />
	<h2 id="subhead">You need to purchase access</h2>
</div>
<p class="blurb">
  Before your trial access expires, you should purchase a permanent access code
	from your campus bookstore. You can purchase one at your campus bookstore
	(ask for [access code name]) or on the bookstore website[URL].
</p>

<?php
if (in_array($paymentStatus, $canEnterCode)) {
    $validApiResponse = true;
  require_once(__DIR__ . "/assessments_activate_code.php");
}
?>

<div class="trial_button_wrapper">
  <p>
    <a href="#">Continue to assessment</a>
  </p>
</div>
