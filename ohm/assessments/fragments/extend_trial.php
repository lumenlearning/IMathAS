<?php
/**
 * This file is included from fragments/activation.php.
 */
?>

<h1 class="greeting">Your trial has expired.</h1>

<div class="trial_button_wrapper">
  <form method="POST" action="<?php echo $GLOBALS['basesiteurl']; ?>/ohm/assessments/process_activation.php">
      <input type="hidden" name="action" value="extend_trial"/>
      <input type="hidden" name="group_id" value="<?php echo $courseOwnerGroupId; ?>"/>
      <input type="hidden" name="course_id" value="<?php echo Sanitize::courseId($courseId); ?>"/>
      <input type="hidden" name="assessment_id" value="<?php echo Sanitize::onlyInt($assessmentId); ?>"/>
      <button id="begin_trial" type="submit" value="Extend Trial">Extend my trial for 24 hours</button>
  </form>
  <br/>
  <a onClick="goBack()">Exit to save my 24 hour trial extension for later</a>
</div>
