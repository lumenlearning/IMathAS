<?php
/**
 * This file is included from fragments/activation.php.
 */

?>

<h1 class="greeting"><span class="emphasis"><?php echo Sanitize::encodeStringForDisplay($userDisplayName); ?></span>, you're all set!</h1>

<p class="blurb">
	Thank you for submitting your Lumen OHM course activation code. You are now
	able to take all Lumen OHM assessments in this course. You will receive a
	confirmation email shortly. In the meantime, please print this screen or save
	it as a PDF for your records.
</p>

<h2 id="subhead">Submission Details</h2>
<br/>
<div id="confirmation-details">
	<p><strong>Student Name: </strong><span class="emphasis"><?php echo Sanitize::encodeStringForDisplay($userfullname); ?></span></p>
	<p><strong>Course Name: </strong><span class="emphasis">Lumen OHM Statistics</span></p>
	<p><strong>Access Code Submitted: </strong><span class="emphasis">X24KTDG99</span></p>
	<p><strong>Timestamps: </strong><span class="emphasis">5:03 PM, 12/6/2017</span></p>
</div>

<div class="trial_button_wrapper">
  <p>
    <a href="<?php echo $GLOBALS['basesiteurl'] . '/assessment/showtest.php?activation_event=continue_trial'; ?>">Continue to assessment</a>
  </p>
</div>
