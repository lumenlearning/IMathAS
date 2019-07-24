<?php
/**
 * This file reached directly by the user via a redirect after a successful code activation.
 */

require_once(__DIR__ . '/../../init.php');
require_once(__DIR__ . '/../../header.php');
require_once(__DIR__ . '/../assessments/payment_lib.php');

use OHM\Assessments\PaymentLib;

if (!isset($_REQUEST['courseId']) || !isset($_REQUEST['activationTime'])) {
    header("Location: " . $GLOBALS['basesiteurl']);
    exit;
}

$courseId = Sanitize::onlyInt($_REQUEST['courseId']);
$assessmentId = Sanitize::onlyInt($_REQUEST['assessmentId']);

// User Name
$userDisplayName = explode(' ', $GLOBALS['userfullname'])[0];
if ('' == trim($userDisplayName)) {
	$userDisplayName = $GLOBALS['username'];
}

// Course name
$courseNameStm = $DBH->prepare("SELECT name FROM imas_courses WHERE id=:id");
$courseNameStm->execute(array(':id'=>$courseId));
$courseName = $courseNameStm->fetchColumn(0);

// Access Code
$accessCode = $_REQUEST['code'];

// Timestamp
$timestamp = $_REQUEST['activationTime'];
$date = new DateTime();

// Output 2013-11-28 19:13:19
$date->setTimestamp($timestamp);
$timestamp_string = $date->format('Y-m-d H:i:s');

$assessmentVersion = PaymentLib::getAssessmentVersion($courseId);
switch ($assessmentVersion) {
	case 1:
		$assessmentUrl = sprintf('%s/assessment/showtest.php?id=%d&cid=%d',
			$GLOBALS['basesiteurl'], $assessmentId, $courseId);
		break;
	case 2:
		$assessmentUrl = sprintf('%s/assess2/?aid=%d&cid=%d',
			$GLOBALS['basesiteurl'], $assessmentId, $courseId);
		break;
	default:
		error_log("In " . __FILE__ . ": Unknown assessment version!");
		break;
}
?>

<div class="access-wrapper">
<div class="access-block">
	<h1 class="greeting">You're all set!</h1>

	<p class="blurb">
    Thank you for submitting your Lumen OHM course activation code. Please print this screen or save it as a PDF for your records.
	</p>

	<h2 id="subhead">Submission Details</h2>
	<div id="confirmation-details">
		<p><strong>Student Name: </strong><?php echo Sanitize::encodeStringForDisplay($GLOBALS['userfullname']); ?></p>
		<p><strong>Course Name: </strong><?php echo Sanitize::encodeStringForDisplay($courseName); ?></p>
        <p><strong>Course ID: </strong><?php echo Sanitize::encodeStringForDisplay($courseId); ?></p>
		<p><strong>Activation Code Used: </strong><span style="text-transform:uppercase;"><?php echo Sanitize::encodeStringForDisplay($accessCode); ?></span></p>
		<p><strong>Timestamp: </strong><?php echo $timestamp_string; ?></p>
	</div>

	<div class="trial_button_wrapper">
	  <p>
	    <a href="<?php echo $assessmentUrl; ?>">Continue to assessment</a>
	  </p>
	</div>

</div><!-- end .access-block -->
</div><!-- end .access-wrapper -->

<?php require_once(__DIR__ . '/../../footer.php'); ?>
