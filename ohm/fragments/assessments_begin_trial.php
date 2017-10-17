<?php
/**
 * This file is included from fragments/assessments_payment.php.
 */
?>
<p><b>If you choose to begin a trial, it will begin immediately.</b></p>

<form method="POST" action="<?php echo $GLOBALS['basesiteurl']; ?>/ohm/assessments_process_payment.php">
    <input type="hidden" name="action" value="begin_trial"/>
    <input type="hidden" name="group_id" value="<?php echo $GLOBALS['groupid']; ?>"/>
    <input type="hidden" name="course_id" value="<?php echo Sanitize::encodeStringForDisplay($_REQUEST['cid']); ?>"/>
    <input type="hidden" name="assessment_id" value="<?php echo Sanitize::encodeStringForDisplay($_REQUEST['id']); ?>"/>
    Click to begin trial (<?php echo $GLOBALS['student_pay_api']['trial_period_human']; ?>)
    <input type="submit" value="Begin Trial"/>
</form>

