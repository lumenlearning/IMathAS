<?php
/**
 * This file is included from fragments/activation.php.
 */
?>
<form method="POST" action="<?php echo $GLOBALS['basesiteurl']; ?>/ohm/assessments/process_activation.php">
    <input type="hidden" name="action" value="activate_code"/>
    <input type="hidden" name="group_id" value="<?php echo $courseOwnerGroupId; ?>"/>
    <input type="hidden" name="course_id" value="<?php echo Sanitize::courseId($courseId); ?>"/>
    <input type="hidden" name="assessment_id" value="<?php echo Sanitize::onlyInt($assessmentId); ?>"/>
    <div class="access_code_input_wrapper">
      <label for="access_code">Already have your activation code?</label>
      <input type="text" name="access_code" id="access_code" placeholder="ENTER CODE"/>
      <button type="submit" id="access_code_submit">Activate</button>
    </div>
</form>
