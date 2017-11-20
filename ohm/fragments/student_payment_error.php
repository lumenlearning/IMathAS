<?php
/**
 * This file is included from fragments/assessments_payment.php.
 */

echo "<p id='error-text'>An error occurred while checking assessment access code status.</p>";

if (isset($GLOBALS['studentPayUserMessage'])) {
	printf("<p>Error message: %s</p>", $GLOBALS['studentPayUserMessage']);
}

?>

<div class="trial_button_wrapper">
	<a onClick="goBack()"> Go Back</a>
</div>
