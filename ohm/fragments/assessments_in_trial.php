<?php
/**
 * This file is included from fragments/assessments_payment.php.
 */

$bookstoreUrl = "http://wsubookie.bncollege.com/webapp/wcs/stores/servlet/BNCBHomePage?storeId=15064&catalogId=10001&langId=-1";
$trialTimeRemaining = gmdate("z days, H hours, i minutes",
    $studentPayStatus->getStudentTrialTimeRemainingSeconds());
?>

<h1 class="greeting"><span class="emphasis"><?php echo $userDisplayName; ?></span>, you have <span class="emphasis"><?php echo $trialTimeRemaining; ?></span> remaining in your trial!</h1>
<div class="sub-wrapper">
	<img id="hourglass-icon" src="<?php echo $GLOBALS['basesiteurl'] . '/ohm/img/hourglass.png'; ?>" alt="hourglass icon" />
	<h2 id="subhead">You need to purchase access</h2>
</div>
<p class="blurb">
  Before your trial access expires, you should purchase a permanent access code
	from your campus bookstore. You can purchase one at your campus bookstore
	(ask for <span class="emphasis">OHM Platform Access Code</span>) or on the <a href="<?php echo $bookstoreUrl; ?>">bookstore website</a>.
</p>

<?php
if (in_array($paymentStatus, $canEnterCode)) {
    $validApiResponse = true;
  require_once(__DIR__ . "/assessments_activate_code.php");
}
?>

<div class="trial_button_wrapper">
  <p>
    <a onClick="goBack()">Continue to assessment</a>
  </p>
</div>
