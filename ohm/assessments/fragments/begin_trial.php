<?php
/**
 * This file is included from fragments/activation.php.
 */

$assessNameStm = $DBH->prepare("SELECT name FROM imas_assessments WHERE id=:id AND courseid=:courseid LIMIT 1");
$assessNameStm->execute(array(':id'=>$assessmentId, ':courseid'=>$courseId));
$assessmentName = $assessNameStm->fetchColumn(0);
?>

<h1 class="greeting">Enter a Lumen OHM course activation code.</h1>

<div class="trial_button_wrapper">
  <form method="POST" action="<?php echo $GLOBALS['basesiteurl']; ?>/ohm/assessments/process_activation.php">
      <input type="hidden" name="action" value="begin_trial"/>
      <input type="hidden" name="group_id" value="<?php echo $courseOwnerGroupId; ?>"/>
      <input type="hidden" name="course_id" value="<?php echo Sanitize::courseId($courseId); ?>"/>
      <input type="hidden" name="assessment_id" value="<?php echo Sanitize::onlyInt($assessmentId); ?>"/>
      <button id="begin_trial" type="submit">Begin Trial (<?php echo $GLOBALS['student_pay_api']['trial_period_human']; ?>)</button>
  </form>
  <br/>
  <p>
    <a onClick="goBack()">I'll start my trial later</a>
  </p>
</div>
