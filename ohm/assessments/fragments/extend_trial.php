<?php
/**
 * This file is included from fragments/activation.php.
 */
?>

<h1 class="greeting">Your trial has expired.</h1>

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
    <p class="emphasis">In a pinch?</p>
    <p>
      Just in case you need a little more time, you may use a one-time 24 hour
      pass. You can use it now or save it for later.
    </p>
    <p>
      You only get one pass, so be sure to purchase an activation code through
      your campus bookstore soon!
    </p>
    <div class="trial_button_wrapper">
      <form method="POST" action="<?php echo $GLOBALS['basesiteurl']; ?>/ohm/assessments/process_activation.php">
          <input type="hidden" name="action" value="extend_trial"/>
          <input type="hidden" name="group_id" value="<?php echo $GLOBALS['groupid']; ?>"/>
          <input type="hidden" name="course_id" value="<?php echo Sanitize::courseId($courseId); ?>"/>
          <input type="hidden" name="assessment_id" value="<?php echo Sanitize::onlyInt($assessmentId); ?>"/>
          <button id="begin_trial" type="submit">Use 24 hour pass</button>
      </form>
    </div>
  </div>
</div>
