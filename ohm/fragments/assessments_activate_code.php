<?php
/**
 * This file is included from fragments/assessments_payment.php.
 */
?>
<form method="POST" action="<?php echo $GLOBALS['basesiteurl']; ?>/ohm/assessments_process_payment.php">
    <input type="hidden" name="action" value="activate_code"/>
    <input type="hidden" name="group_id" value="<?php echo $GLOBALS['groupid']; ?>"/>
    <input type="hidden" name="course_id" value="<?php echo Sanitize::encodeStringForDisplay($_REQUEST['cid']); ?>"/>
    <input type="hidden" name="assessment_id" value="<?php echo Sanitize::encodeStringForDisplay($_REQUEST['id']); ?>"/>
    Access code:
    <input type="text" name="access_code" placeholder="Enter access code"/>
    <input type="submit" value="Activate"/>
</form>

