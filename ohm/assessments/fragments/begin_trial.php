<?php
/**
 * This file is included from fragments/activation.php.
 */

$assessNameStm = $DBH->prepare("SELECT name FROM imas_assessments WHERE id=:id AND courseid=:courseid LIMIT 1");
$assessNameStm->execute(array(':id'=>$assessmentId, ':courseid'=>$courseId));
$assessmentName = $assessNameStm->fetchColumn(0);
?>

<h1 class="greeting"><span class="emphasis"><?php echo Sanitize::encodeStringForDisplay($userDisplayName); ?></span>, are you ready to start working on <span class="emphasis"><?php echo Sanitize::encodeStringForDisplay($assessmentName); ?></span>?</h1>
<div class="sub-wrapper">
	<img id="hourglass-icon" src="<?php echo $GLOBALS['basesiteurl'] . '/ohm/img/hourglass.png'; ?>" alt="hourglass icon" />
	<h2 id="subhead">Ready to start your 2 week trial?</h2>
</div>
<p class="blurb">
	<span class="emphasis"><?php echo Sanitize::encodeStringForDisplay($assessmentName); ?></span>, is
	an assessment powered by Lumen OHM.  By starting you will begin your 2 week
	trial for the assessments in this Lumen OHM course.  Before your trial runs
	out you should get an OHM course activation code
    <?php require(__DIR__ . '/code_purchase_location.php'); ?>
</p>

<?php
if (in_array($paymentStatus, $canEnterCode)) {
    $validApiResponse = true;
  require_once(__DIR__ . "/activate_code.php");
}
?>

<div class="trial_button_wrapper">
  <form method="POST" action="<?php echo $GLOBALS['basesiteurl']; ?>/ohm/assessments/process_activation.php">
      <input type="hidden" name="action" value="begin_trial"/>
      <input type="hidden" name="group_id" value="<?php echo $GLOBALS['groupid']; ?>"/>
      <input type="hidden" name="course_id" value="<?php echo Sanitize::courseId($courseId); ?>"/>
      <input type="hidden" name="assessment_id" value="<?php echo Sanitize::onlyInt($assessmentId); ?>"/>
      <button id="begin_trial" type="submit">Begin Trial (<?php echo $GLOBALS['student_pay_api']['trial_period_human']; ?>)</button>
  </form>
  <br/>
  <p>
    <a onClick="goBack()">I'll start my trial later</a>
  </p>
</div>
