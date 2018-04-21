<?php
/**
 * Enforce all applicable Lumen OHM student payment rules.
 * (activation codes, direct payments, trials, etc)
 *
 * This function should be used in places where an assessment is about
 * to be displayed to the student.
 */

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

$courseId = $sessiondata['courseid'];
$courseName = $sessiondata['coursename'];
$assessmentId = isset($_GET['id']) ? intval($_GET['id']) : $assessmentIdFromDb;

$courseOwnerGroupId = null;
if (isStudentPayEnabled()) {
	require_once(__DIR__ . "/../../ohm/includes/StudentPaymentDb.php");
	$studentPaymentDb = new \OHM\StudentPaymentDb(null, $courseId, null);
	$courseOwnerGroupId = $studentPaymentDb->getCourseOwnerGroupId();

	// We need the course owner's group ID before we can check a student's access code status.
	if (!isValidGroupIdForStudentPayments($courseOwnerGroupId)) {
		error_log(sprintf(
			"Course owner is not a member of a group for course ID %d. Unable to get student access code status.",
			$courseId));
	}
}

$studentPayment = null;
$studentPayStatus = null;
if (isStudentPayEnabled() && isValidGroupIdForStudentPayments($courseOwnerGroupId)) {
	require_once(__DIR__ . "/../../ohm/includes/StudentPayment.php");
	require_once(__DIR__ . "/../../ohm/models/StudentPayStatus.php");
	$studentPayment = new \OHM\StudentPayment($courseOwnerGroupId, $GLOBALS['cid'], $GLOBALS['userid']);

	try {
		$studentPayStatus = $studentPayment->getCourseAndStudentPaymentInfo();
	} catch (OHM\StudentPaymentException $e) {
		// See notes above re: business decisions
		error_log("Student payment API error: " . $e->getMessage());
		error_log("Stack trace: " . $e->getTraceAsString());
	}

	if (!is_null($studentPayStatus) && isStartingAssessment()) {
		$courseRequiresPayment = $studentPayStatus->getCourseRequiresStudentPayment();
		$studentHasAccessCode = $studentPayStatus->getStudentHasValidAccessCode();
		$paymentTypeRequired = $studentPayStatus->getStudentPaymentTypeRequired();

		if (\OHM\StudentPayApiResult::ACCESS_TYPE_ACTIVATION_CODE ==
			$paymentTypeRequired && $courseRequiresPayment) {
			if (!$studentHasAccessCode) {
				require_once(__DIR__ . "/../../ohm/assessments/activation.php");
			}
		}

		if (\OHM\StudentPayApiResult::ACCESS_TYPE_DIRECT_PAY ==
			$paymentTypeRequired && $courseRequiresPayment) {
			if (!$studentHasAccessCode) {
				require_once(__DIR__ . "/../../ohm/assessments/direct_pay.php");
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

