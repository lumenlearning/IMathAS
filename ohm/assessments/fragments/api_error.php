<?php
/**
 * This file is included from ohm/assessments/process_activation.php.
 */
?>

<h1 class="greeting"><span class="emphasis">Activation error</span></h1>
<p class="blurb">
    An error occurred while attempting to activate your trial or access code.<br/>
    Please check your access code or contact support.
</p>

<?php

if (isset($studentPayUserMessage)) {
	printf('<p class="blurb">Error: %s</p>', $studentPayUserMessage);
}
?>

<div class="trial_button_wrapper">
    <a onClick="goBack()"> Go Back</a>
</div>

