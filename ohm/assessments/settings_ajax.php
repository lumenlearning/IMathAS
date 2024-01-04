<?php
/**
 * This file is responsible for changing settings related to student payments.
 *
 * It is expected consumers of this file will be AJAX clients.
 * All responses are in JSON format.
 */

namespace OHM\Assessments;

require_once(__DIR__ . '/../../init.php');

use OHM\Models\StudentPayApiResult;
use OHM\Includes\StudentPaymentDb;
use OHM\Includes\StudentPaymentApi;
use OHM\Exceptions\StudentPaymentException;

$validActions = array('setGroupPaymentType');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : NULL;

// Check if we have valid action
if (!in_array($action, $validActions)) {
	response(400, 'No valid action specified.');
}

if ("setGroupPaymentType" == $action) {
	$paymentType = validParamsPaymentType();
	$groupId = validParamsGroupId();

    if (StudentPayApiResult::ACCESS_TYPE_NOT_REQUIRED == $paymentType) {
        deleteStudentPaymentSetting($groupId);
    } else {
        setStudentPaymentType($groupId, $paymentType);
    }

	$newDbState = StudentPayApiResult::ACCESS_TYPE_NOT_REQUIRED == $paymentType ? false : true;
	setStudentPaymentEnabled($groupId, $newDbState);

	response(200, 'OK');

	exit;
}

// If we get here, something went wrong. Send error response.
response(400, "Unknown action specified.");


/**
 * Return a response to the client.
 *
 * @param $status integer The HTTP status to return.
 * @param $msg string The human-readable message to return.
 */
function response($status, $msg)
{
	header('Content-Type: application/json');
	http_response_code($status);

	echo json_encode(array(
		'message' => $msg
	));

	exit;
}


/**
 * Get and validate the group ID. If validation fails, we immediately return
 * HTTP status 400 with an explanation.
 *
 * @return integer The group ID.
 */
function validParamsGroupId()
{
	$groupId = isset($_REQUEST['groupId']) ? $_REQUEST['groupId'] : NULL;
	$groupId = \Sanitize::onlyInt($groupId);

	if (is_null($groupId) || 1 > $groupId) {
		response(400, "Invalid group ID provided: " . $_REQUEST['groupId']);
	}

	return $groupId;
}


/**
 * Get and validate the student payment / access type. If validation fails, we
 * immediately return HTTP status 400 with an explanation.
 *
 * @return string The student payment / access type.
 */
function validParamsPaymentType()
{
	$paymentType = isset($_REQUEST['paymentType']) ? $_REQUEST['paymentType'] : NULL;
	$paymentType = \Sanitize::simpleString($paymentType);

	if (is_null($paymentType) || empty($paymentType)) {
		response(400, "No payment type provided.");
	}

	return $paymentType;
}


/**
 * Update the student payment setting in the OHM database.
 *
 * @param $courseOwnerGroupId integer The group's ID. (imas_groups, id column)
 * @param $isEnabled boolean True for enabled, false for disabled.
 */
function setStudentPaymentEnabled(int $courseOwnerGroupId, bool $isEnabled): void
{
	try {
		$studentPaymentDb = new StudentPaymentDb(null, null, null, $courseOwnerGroupId, null);
        logStudentPaymentSettingChange($studentPaymentDb, $courseOwnerGroupId, $isEnabled);
		$studentPaymentDb->setStudentPaymentAllCoursesByGroupId($courseOwnerGroupId, $isEnabled);
		$studentPaymentDb->setGroupRequiresStudentPayment($isEnabled);
	} catch (\PDOException $e) {
		dbException($e, $courseOwnerGroupId);
	} catch (StudentPaymentException $e) {
		dbException($e, $courseOwnerGroupId);
	}
}

/**
 * Log changes to the student payment setting for a group.
 *
 * @param StudentPaymentDb $studentPaymentDb An instance of StudentPaymentDb set to the group ID
 *                                           whose student payment setting is being changed.
 * @param int $courseOwnerGroupId The group ID whose student payment setting is being changed.
 * @param bool $isEnabled The new payment setting.
 * @return void
 * @throws StudentPaymentException
 */
function logStudentPaymentSettingChange(StudentPaymentDb $studentPaymentDb, int $courseOwnerGroupId, bool $isEnabled): void
{
    global $username; // This is set by validate.php.

    $isEnabledAsString = $isEnabled ? 'true' : 'false';
    $courseOwnerGroupName = $studentPaymentDb->getCourseOwnerGroupName();
    $logMessage = sprintf('(OHM UI) Setting payments enabled to %s for %s (group ID %d). Change submitted by user "%s".',
        $isEnabledAsString, $courseOwnerGroupName, $courseOwnerGroupId, $username);
    error_log($logMessage);
}

/**
 * This function is called in setStudentPaymentEnabled try/catch blocks.
 *
 * @param $exception \PDOException | StudentPaymentException
 * @param $groupId integer The group ID we attempted to update.
 */
function dbException($exception, $groupId)
{
	error_log(sprintf("Failed to change student payment setting (in OHM database) for group ID %d. Exception: %s",
		$groupId, $exception->getMessage()));
	error_log($exception->getTraceAsString());
	response(500, 'Failed to change student payment setting for group ID ' . $groupId);
}


/**
 * Set the payment / access type in the student payment API.
 *
 * As of 2018 Mar 2, valid payment types are:
 * - "not_required"
 * - "activation_code"
 * - "direct_pay"
 *
 * @param $courseOwnerGroupId integer The group's ID. (imas_groups, id column)
 * @param $paymentType string The payment type.
 */
function setStudentPaymentType($courseOwnerGroupId, $paymentType)
{
	$studentPaymentApi = new StudentPaymentApi(null, null, null, $courseOwnerGroupId, null);

	try {
		$apiResult = $studentPaymentApi->updateGroupPaymentSettings($paymentType);

		if ($apiResult->getErrors()) {
			response(500, 'Failed to change student payment setting for group ID ' . $courseOwnerGroupId);
		}
	} catch (StudentPaymentException $e) {
		error_log(sprintf("Failed to change student payment setting (in student payment API) for group ID %d. Exception: %s",
            $courseOwnerGroupId, $e->getMessage()));
		error_log($e->getTraceAsString());
		response(500, 'Failed to change student payment setting for group ID ' . $courseOwnerGroupId);
	}
}

/**
 * Delete a payment setting in the student payment API.
 *
 * @param int $courseOwnerGroupId The group's ID. (imas_groups, id column)
 * @return void
 */
function deleteStudentPaymentSetting($courseOwnerGroupId) {
    $studentPaymentApi = new StudentPaymentApi(null, null, null, $courseOwnerGroupId, null);

    /*
     * A warning from May 17, 2018:
     *   If we're disabling student payments in OHM, don't delete it in the student
     *   payment API. Associated data now exists in the payment service that OHM
     *   doesn't know about.
     *
     * As of Jan 3, 2024, I did not find what may have been referenced here.
     */

    try {
        $apiResult = $studentPaymentApi->deleteGroupPaymentSettings();

        if ($apiResult->getErrors()) {
            response(500, 'Failed to delete student payment setting for group ID ' . $courseOwnerGroupId);
        }
    } catch (StudentPaymentException $e) {
        error_log(sprintf("Failed to delete student payment setting (in student payment API) for group ID %d. Exception: %s",
            $courseOwnerGroupId, $e->getMessage()));
        error_log($e->getTraceAsString());
        response(500, 'Failed to delete student payment setting for group ID ' . $courseOwnerGroupId);
    }
}
