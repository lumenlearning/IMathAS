<?php
/**
 * This file is included from fragments/assessments_payment.php.
 */

$assessNameStm = $DBH->prepare("SELECT name FROM imas_assessments WHERE id=:id AND courseid=:courseid LIMIT 1");
$assessNameStm->execute(array(':id'=>Sanitize::onlyInt($_REQUEST['id']), ':courseid'=>$cid));
$assessmentName = $assessNameStm->fetchColumn(0);
?>

<h1 class="greeting"><span class="emphasis"><?php echo $userDisplayName; ?></span>, are you ready to start working on <span class="emphasis"><?php echo $assessmentName; ?></span>?</h1>
<div class="sub-wrapper">
	<img id="hourglass-icon" src="<?php echo $GLOBALS['basesiteurl'] . '/ohm/img/hourglass.png'; ?>" alt="hourglass icon" />
	<h2 id="subhead">You need to purchase access</h2>
</div>
<p class="blurb">
  You are about to open <span class="emphasis"><?php echo $assessmentName; ?></span>, which is an
  assessment powered by Lumen  OHM.  By opening this assessment, you will begin
  your 2 week trial access.  Before your trial runs out, you’ll need to
  purchase your access code from your campus bookstore. Ask for:
  <span class="emphasis">OHM Platform Access Code</span>.
</p>

<?php
if (in_array($paymentStatus, $canEnterCode)) {
    $validApiResponse = true;
  require_once(__DIR__ . "/assessments_activate_code.php");
}
?>

<div class="trial_button_wrapper">
  <form method="POST" action="<?php echo $GLOBALS['basesiteurl']; ?>/ohm/assessments_process_payment.php">
      <input type="hidden" name="action" value="begin_trial"/>
      <input type="hidden" name="group_id" value="<?php echo $GLOBALS['groupid']; ?>"/>
      <input type="hidden" name="course_id" value="<?php echo Sanitize::courseId($_REQUEST['cid']); ?>"/>
      <input type="hidden" name="assessment_id" value="<?php echo Sanitize::onlyInt($_REQUEST['id']); ?>"/>
      <button id="begin_trial" type="submit">Begin Trial (<?php echo $GLOBALS['student_pay_api']['trial_period_human']; ?>)</button>
  </form>
  <br/>
  <p>
    <a onClick="goBack()">I'll start my trial later</a>
  </p>
</div>
