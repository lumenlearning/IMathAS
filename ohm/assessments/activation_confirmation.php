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
	<h1 class="greeting"><span class="emphasis"><?php echo Sanitize::encodeStringForDisplay($userDisplayName); ?></span>,
        you're all set!</h1>

	<p class="blurb">
        Thank you for submitting your Lumen OHM course activation code. You will receive
        a confirmation email shortly. In the meantime, please print this screen or save
        it as a PDF for your records.
	</p>

	<h2 id="subhead">Submission Details</h2>
	<br/>
	<div id="confirmation-details">
		<p><strong>Student Name: </strong><span class="emphasis"><?php echo Sanitize::encodeStringForDisplay($userfullname); ?></span></p>
		<p><strong>Course Name: </strong><span class="emphasis"><?php echo Sanitize::encodeStringForDisplay($courseName); ?></span></p>
		<p><strong>Access Code Submitted: </strong><span class="emphasis" style="text-transform:uppercase;"><?php echo Sanitize::encodeStringForDisplay($accessCode); ?></span></p>
		<p><strong>Timestamp: </strong><span class="emphasis"><?php echo $timestamp_string; ?></span></p>
	</div>

	<div class="trial_button_wrapper">
	  <p>
	    <a href="<?php echo $GLOBALS['basesiteurl'] . '/assessment/showtest.php'; ?>">Continue to assessment</a>
	  </p>
	</div>

</div><!-- end .access-block -->
</div><!-- end .access-wrapper -->

<?php require_once(__DIR__ . '/../../footer.php'); ?>
