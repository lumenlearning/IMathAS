<?php
/**
 * This file is included from fragments/activation.php.
 */

$assessNameStm = $DBH->prepare("SELECT name FROM imas_assessments WHERE id=:id AND courseid=:courseid LIMIT 1");
$assessNameStm->execute(array(':id'=>$assessmentId, ':courseid'=>$courseId));
$assessmentName = $assessNameStm->fetchColumn(0);
?>

<h1 class="greeting">Enter a Lumen OHM course activation code.</h1>

<div class="access-sub-block">
    <div class="access-sub-block-left">
		<?php
		if (in_array($paymentStatus, $canEnterCode)) {
			$validApiResponse = true;
			require_once(__DIR__ . "/activate_code.php");
		}
		?>
    </div>
    <div class="access-sub-block-right">
        <p class="emphasis">Need an activation code?</p>
        <p>
            Purchase your Lumen OHM course activation code, sold exclusively <?php require(__DIR__ . '/code_purchase_location.php'); ?>
        </p>
        <p>
            Until then, if you need to access your assessments, you can start your
            two-week trial.
        </p>
        <div class="trial_button_wrapper">
            <form method="POST" action="<?php echo $GLOBALS['basesiteurl']; ?>/ohm/assessments/process_activation.php">
                <input type="hidden" name="action" value="begin_trial"/>
                <input type="hidden" name="group_id" value="<?php echo Sanitize::onlyInt($courseOwnerGroupId); ?>"/>
                <input type="hidden" name="course_id" value="<?php echo Sanitize::courseId($courseId); ?>"/>
                <input type="hidden" name="assessment_id" value="<?php echo Sanitize::onlyInt($assessmentId); ?>"/>
                <button id="begin_trial" type="submit">Start two week trial</button>
            </form>
        </div>
    </div>
</div>
