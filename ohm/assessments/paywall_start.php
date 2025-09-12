<?php
/**
 * Enforce all applicable Lumen OHM student payment rules.
 * (activation codes, direct payments, trials, etc)
 *
 * This file should be require()'d in places where an assessment is about
 * to be displayed to the student.
 */

require_once(__DIR__ . '/../assessments/payment_lib.php');

use OHM\Includes\StudentPayment;
use OHM\Includes\StudentPaymentDb;
use OHM\Exceptions\StudentPaymentException;
use OHM\Models\StudentPayApiResult;
use OHM\Assessments\PaymentLib;

/*
 * So I won't forget for a 6th time and have to delete an hour of nice refactoring:
 *
 * This is all in global scope because we are using core MOM's validate.php, header.php,
 * and footer.php files. These all depend on many things declared in global scope. Don't
 * remove this OHM-specific code block from global scope unless you're prepared to deal
 * with that.
 */

// The business decision is to allow students through to assessments if we encounter any
// problems checking access codes. This includes failure to interact with the payment API.

$userId = $GLOBALS['userid'];
$courseId = isset($_GET['cid']) ? intval($_GET['cid']) : $_SESSION['courseid'];

$assessmentVersion = PaymentLib::getAssessmentVersion($courseId);
switch ($assessmentVersion) {
	case 1:
		$assessmentId = isset($_GET['id']) ? intval($_GET['id']) : $assessmentIdFromDb;
		break;
	case 2:
		$assessmentId = isset($_GET['aid']) ? intval($_GET['aid']) : $assessmentIdFromDb;
		break;
	default:
		error_log("In " . __FILE__ . ": Unknown assessment version!");
		break;
}

$courseNameStm = $DBH->prepare("SELECT name FROM imas_courses WHERE id=:id");
$courseNameStm->execute(array(':id' => $courseId));
$courseName = $courseNameStm->fetchColumn(0);


if (isStudentPayEnabled() && isTutor($userId, $courseId)) {
	error_log(sprintf('Student ID %d is a tutor in course ID %d. Paywall will be bypassed.',
		$userId, $courseId));
}

$courseOwnerGroupId = null;
$courseOwnerGroupGuid = null;
$enrollmentId = null;
if (isStudentPayEnabled() && !isTutor($userId, $courseId)) {
	$studentPaymentDb = new StudentPaymentDb(null, $courseId, $GLOBALS['userid'], null, null);
	$courseOwnerGroupId = $studentPaymentDb->getCourseOwnerGroupId();
	$courseOwnerGroupGuid = $studentPaymentDb->getGroupGuid($courseOwnerGroupId);
	$enrollmentId = $studentPaymentDb->getStudentEnrollmentId();

	// We need the course owner's group ID before we can check a student's access code status.
	if (!isValidGroupIdForStudentPayments($courseOwnerGroupId)) {
		error_log(sprintf(
			"Course owner is not a member of a group for course ID %d. Unable to get student access code status.",
			$courseId));
	}
}

$studentPayment = null;
$studentPayStatus = null;
if (isStudentPayEnabled() && !isTutor($userId, $courseId) && isValidGroupIdForStudentPayments($courseOwnerGroupId)) {
	$studentPayment = new StudentPayment(null, $GLOBALS['cid'], $GLOBALS['userid'], $courseOwnerGroupId, null);

	try {
		$studentPayStatus = $studentPayment->getCourseAndStudentPaymentInfo();
	} catch (StudentPaymentException $e) {
		// See notes above re: business decisions
		error_log("Student payment API error: " . $e->getMessage());
		error_log("Stack trace: " . $e->getTraceAsString());
	}

	if (!is_null($studentPayStatus) && PaymentLib::isStartingAssessment()) {
		$courseRequiresPayment = $studentPayStatus->getCourseRequiresStudentPayment();
		$studentHasAccessCode = $studentPayStatus->getStudentHasValidAccessCode();
		$paymentTypeRequired = $studentPayStatus->getStudentPaymentTypeRequired();
        $studentIsOptedOut = $studentPayStatus->getStudentIsOptedOut();

        if ($studentIsOptedOut && !$studentHasAccessCode) {
            // This should happen even if payments are not enabled at the group level.
            require_once(__DIR__ . "/../../ohm/assessments/direct_or_multi_pay.php");
        }

		else if (StudentPayApiResult::ACCESS_TYPE_ACTIVATION_CODE ==
			$paymentTypeRequired && $courseRequiresPayment) {
			if (!$studentHasAccessCode) {
				require_once(__DIR__ . "/../../ohm/assessments/activation.php");
			}
		}

		else if (needsLumenComponents($paymentTypeRequired) &&
			$paymentTypeRequired && $courseRequiresPayment) {
			if (!$studentHasAccessCode) {
				require_once(__DIR__ . "/../../ohm/assessments/direct_or_multi_pay.php");
			}
		}

	}
}

if (!is_null($studentPayStatus) && $studentPayStatus->getStudentIsInTrial()) {
	$shouldLogEvent = array('begin_trial', 'extend_trial', 'continue_trial');
	$logEventType = getActivationLogEventType();

	if (in_array($logEventType, $shouldLogEvent)) {
		$studentPayment->logTakeAssessmentDuringTrial($assessmentId);
		unsetActivationLogEventType();
	}
}


/**
 * Determine if student payment status should be checked for assessments.
 *
 * @return boolean True if yes, False if no.
 */
function isStudentPayEnabled()
{
	// 20 = student
	if (20 > $GLOBALS['myrights'] && isset($GLOBALS['student_pay_api']) && $GLOBALS['student_pay_api']['enabled']) {
		return true;
	}

	return false;
}

/**
 * Determine if a group ID is valid for student payments.
 *
 * @param $groupId integer The group ID.
 * @return bool True if yes, False if no.
 */
function isValidGroupIdForStudentPayments($groupId)
{
	if (is_null($groupId) || 0 >= $groupId) {
		return false;
	}

	return true;
}

/**
 * Determine if the current user is a tutor in any capacity.
 *
 * @param $userId integer The user's ID from imas_users.
 * @param $courseId integer The course's ID from imas_courses.
 * @return bool True if the user is a tutor, false if not.
 */
function isTutor($userId, $courseId)
{
	return isGlobalTutor() || isTutorInCourse($userId, $courseId);
}

/**
 * Determine if the current user is a global tutor.
 *
 * @return bool True if the user is a global tutor, false if not.
 */
function isGlobalTutor()
{
	return 15 <= $GLOBALS['myrights'];
}

/**
 * Determine if a user is a tutor in a course.
 *
 * @param $userId integer The user's ID in imas_users.
 * @param $courseId integer The course ID in imas_courses.
 * @return bool bool True if the user is a tutor in the course. False if not.
 */
function isTutorInCourse($userId, $courseId)
{
	$stm = $GLOBALS['DBH']->prepare('SELECT id FROM imas_tutors WHERE userid=:userId AND courseid=:courseId');
	$stm->execute(array(
		':userId' => $userId,
		':courseId' => $courseId
	));
	$result = $stm->fetchColumn(0);

	if (null == $result || 1 > $result) {
		return false;
	} else {
		return true;
	}
}

/**
 * Determine if Lumen Components should be used to handle student payments.
 *
 * @param $paymentTypeRequired
 * @return bool
 */
function needsLumenComponents($paymentTypeRequired) {
	return in_array($paymentTypeRequired, array(
		StudentPayApiResult::ACCESS_TYPE_DIRECT_PAY,
		StudentPayApiResult::ACCESS_TYPE_MULTI_PAY
	));
}

/**
 * Return the student payments type of event we want to log.
 *
 * This information is obtained from a session cookie or a query string value.
 *
 * @return null
 */
function getActivationLogEventType()
{
	if (isset($_COOKIE['activation_event'])) {
		return $_COOKIE['activation_event'];
	} else if (!is_null($_REQUEST['activation_event'])) {
		return $_REQUEST['activation_event'];
	} else {
		return null;
	}
}

function unsetActivationLogEventType()
{
	setcookie("activation_event", "", -1, '/');
	unset($_COOKIE['activation_event']);
}

