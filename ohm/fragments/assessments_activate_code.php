<?php
/**
 * This file is included from fragments/assessments_payment.php.
 */
?>
<form method="POST" action="<?php echo $GLOBALS['basesiteurl']; ?>/ohm/assessments_process_payment.php">
    <input type="hidden" name="action" value="activate_code"/>
    <input type="hidden" name="group_id" value="<?php echo $GLOBALS['groupid']; ?>"/>
    <input type="hidden" name="course_id" value="<?php echo Sanitize::courseId($_REQUEST['cid']); ?>"/>
    <input type="hidden" name="assessment_id" value="<?php echo Sanitize::onlyInt($_REQUEST['id']); ?>"/>
    <div class="access_code_input_wrapper">
      <label for="access_code">Already have an access code?</label>
      <input type="text" name="access_code" id="access_code" placeholder="ENTER CODE"/>
      <button type="submit" id="access_code_submit">Activate</button>
    </div>
</form>
