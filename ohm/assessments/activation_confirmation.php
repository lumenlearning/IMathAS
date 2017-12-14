<?php
/**
 * This file is included from fragments/activation.php.
 */

require_once(__DIR__ . '/../../init.php');
require_once(__DIR__ . '/../../header.php');

if (!isset($_COOKIE['stupayasscode'])) {
    header("Location: " . $GLOBALS['basesiteurl']);
    exit;
}

// User Name
$userDisplayName = explode(' ', $GLOBALS['userfullname'])[0];
if ('' == trim($userDisplayName)) {
	$userDisplayName = $GLOBALS['username'];
}

// Course name
$courseNameStm = $DBH->prepare("SELECT name FROM imas_courses WHERE id=:id");
$courseNameStm->execute(array(':id'=>$_REQUEST['courseId']));
$courseName = $courseNameStm->fetchColumn(0);

// Access Code
$accessCode = $_COOKIE['stupayasscode'];

// Timestamp
$timestamp = $_COOKIE['stupayasscodetimestamp'];
$date = new DateTime();

// Output 2013-11-28 19:13:19
$date->setTimestamp($timestamp);
$timestamp_string = $date->format('Y-m-d H:i:s');

?>

<div class="access-wrapper">
<div class="access-block">
	<h1 class="greeting">You're all set!</h1>

	<p class="blurb">
    Thank you for submitting your Lumen OHM course activation code. You will receive
    a confirmation email shortly. Please print this screen or save it as a
    PDF for your records.
	</p>

	<h2 id="subhead">Submission Details</h2>
	<div id="confirmation-details">
		<p><strong>Student Name: </strong><?php echo Sanitize::encodeStringForDisplay($userfullname); ?></p>
		<p><strong>Course Name: </strong><?php echo Sanitize::encodeStringForDisplay($courseName); ?></p>
		<p><strong>Access Code Used: </strong><span style="text-transform:uppercase;"><?php echo Sanitize::encodeStringForDisplay($accessCode); ?></span></p>
		<p><strong>Timestamp: </strong><?php echo $timestamp_string; ?></p>
	</div>

	<div class="trial_button_wrapper">
	  <p>
	    <a href="<?php echo $GLOBALS['basesiteurl'] . '/assessment/showtest.php'; ?>">Continue to assessment</a>
	  </p>
	</div>

</div><!-- end .access-block -->
</div><!-- end .access-wrapper -->

<?php require_once(__DIR__ . '/../../footer.php'); ?>
