<?php
/**
 * This file is included from fragments/assessments_payment.php.
 */

echo "<p>An error occurred while checking assessment access code status.</p>";

if (isset($GLOBALS['studentPayUserMessage'])) {
	printf("<p>Error message: %s</p>", $GLOBALS['studentPayUserMessage']);
}

printf('<a href="%s">Back</a>', $assessmentUrl);

