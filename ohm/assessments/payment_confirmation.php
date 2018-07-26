<?php

if (!isset($_COOKIE['ohm_payment_confirmation'])) {
	header('Location: ' . $GLOBALS['basesiteurl']);
	exit;
}

require_once(__DIR__ . "/../../init.php");
require_once(__DIR__ . "/../../header.php");

use OHM\Includes\StudentPaymentApi;
use OHM\Exceptions\StudentPaymentException;
use OHM\Models\StudentPayApiResult;


$cookieData = json_decode($_COOKIE['ohm_payment_confirmation'], true);

$confirmationNum = $cookieData['confNum'];
$activationCode = $cookieData['code'];
$groupId = $cookieData['gid'];
$courseId = $cookieData['cid'];
$assessmentId = $cookieData['aid'];
$userEmail = $cookieData['email'];

$redirectTo = sprintf('%s/assessment/showtest.php?id=%d&cid=%d',
	$GLOBALS['basesiteurl'], $assessmentId, $courseId);

$institution = getInstitutionData($groupId, $courseId, $userid);
$schoolLogoUrl = $institution->getSchoolLogoUrl();
$attributionLogoUrl = is_null($schoolLogoUrl) || empty($schoolLogoUrl)
	? 'null' : '\'https://s3-us-west-2.amazonaws.com/lumen-components/assets/Lumen-300x138.png\'';

$stm = $DBH->prepare('SELECT name FROM imas_courses WHERE id = :id');
$stm->execute(array(':id' => $courseId));
$courseName = $stm->fetch(\PDO::FETCH_ASSOC)['name'];

?>

    <div id="paymentComponent"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/react/0.13.3/react.min.js"></script>
    <script src="<?php echo $GLOBALS['student_pay_api']['direct_pay_component_url']; ?>"></script>
    <script>
	  directPayComponents.renderDirectPayLandingPage('paymentComponent', {
        'userEmail': '<?php echo $userEmail; ?>',
        'studentName': '<?php echo Sanitize::encodeStringForJavascript($GLOBALS['userfullname']) ?>',
        'courseTitle': '<?php echo $courseName; ?>',
        'activationCode': '<?php echo $activationCode ?>',
        'redirectTo': '<?php echo $redirectTo; ?>',
        'paymentStatus': 'has_access',
        'schoolLogoUrl': '<?php echo $schoolLogoUrl; ?>',
        'attributionLogoUrl': <?php echo $attributionLogoUrl; ?>,
      });
    </script>

<?php
function getInstitutionData($groupId, $courseId, $studentId)
{
	$lumenistrationInstitution = null;
	try {
		$studentPaymentApi = new StudentPaymentApi($groupId, $courseId, $studentId);
		$lumenistrationInstitution = $studentPaymentApi->getInstitutionData();
	} catch (StudentPaymentException $e) {
		error_log("Failed to communicate with Lumenistration. " . $e->getMessage());
		error_log($e->getTraceAsString());
		// Don't break the page.
		$lumenistrationInstitution = new \OHM\Models\LumenistrationInstitution();
	}

	return $lumenistrationInstitution;
}

require_once(__DIR__ . "/../../footer.php");
exit;

