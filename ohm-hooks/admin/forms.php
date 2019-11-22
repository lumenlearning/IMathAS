<?php

use OHM\Models\StudentPayApiResult;
use OHM\Exceptions\StudentPaymentException;
use OHM\Includes\StudentPaymentApi;


/**
 * Return raw HTML to insert directly into <head/>.
 *
 * @return string Raw HTML for insertion into <head/>.
 */
function getHeaderCode()
{
    global $imasroot;

    return '<script type="text/javascript" src="' . $imasroot . '/ohm/js/student_pay/studentPayAjax.js"></script>';

}


/**
 * Render a form snippet for the "create/modify a course" settings page.
 *
 * @param string $action One of "addcourse" or "modify"
 * @param int $userRights The user's rights. (from imas_users)
 * @param int $courseId The ID of the course being created/modified.
 * @throws StudentPaymentException
 */
function getCourseSettingsForm($action, $userRights, $courseId)
{
    renderCourseRequiresStudentPayment($action, $userRights, $courseId);
}


/**
 * Render a form snippet for the "modify a group" page.
 *
 * @param $groupId
 * @param $groupType
 * @param $userRights
 */
function getModGroupForm($groupId, $groupType, $userRights)
{
    global $DBH;

    echo '<input type="checkbox" id="iscust" name="iscust" ';
    if ($groupType == 1) {
        echo 'checked';
    }
    echo '> <label for="iscust">' . _('Lumen Customer') . '</label><br/>';

    // Rights level 100 == admins
    if (100 > $userRights) {
        return;
    }

    $stm = $DBH->prepare("SELECT lumen_guid FROM imas_groups WHERE id = :groupId");
    $stm->execute(array(':groupId' => $_GET['id']));

    $lumenGuid = Sanitize::simpleString($stm->fetchColumn(0));
    printf('Lumen GUID: <input type="text" name="lumen_guid" size="50" value="%s"/><br/>', $lumenGuid);

    if (isset($GLOBALS['student_pay_api']) && $GLOBALS['student_pay_api']['enabled']) {
        $currentAccessType = getGroupAssessmentAccessType($groupId);
        printf('Student payments: %s<br/>', StudentPayApiResult::VALID_ACCESS_TYPES[$currentAccessType]);
    }
}


/*
 * The following are what would normally be private methods.
 */


/**
 * Render the "Assessments require payment or activation" portion of the
 * Create/Modify course settings page.
 *
 * @param string $action One of "addcourse" or "modify"
 * @param int $userRights The user's rights. (from imas_users)
 * @param int $courseId The course's ID, if one is being modified.
 * @throws \OHM\Exceptions\StudentPaymentException
 */
function renderCourseRequiresStudentPayment($action, $userRights, $courseId): void
{
    // Rights level 100 == admins
    if (100 > $userRights || !isset($GLOBALS['student_pay_api'])
        || !$GLOBALS['student_pay_api']['enabled']) {
        return;
    }

    extract($GLOBALS, EXTR_SKIP | EXTR_REFS); // Sadface. :(

    $userId = $GLOBALS['userid'];

    $courseOwnerGroupId = null;
    if ('addcourse' == $action) {
        $courseOwnerGroupId = getUserGroupId($userId);
    }
    if ('modify' == $action) {
        $courseOwnerGroupId = getCourseOwnerGroupId($courseId);
    }

    if (empty($courseOwnerGroupId)) {
        // It's possible for users to have a group ID of "0". (default group)
        // Group 0 doesn't represent any school, so we can't do anything with that.
        return;
    }

    $studentPaymentDb = new \OHM\Includes\StudentPaymentDb($courseOwnerGroupId, $courseId, null);
    $groupRequiresPayment = $studentPaymentDb->getGroupRequiresStudentPayment();
    if ($groupRequiresPayment && 'addcourse' != $action) {
        $checked = $studentPaymentDb->getCourseRequiresStudentPayment() ? 'checked' : '';
        echo '<span class=form>Assessments require payment or activation?</span><span class=formright>';
        printf('<input type="checkbox" id="studentpay" name="studentpay" %s/>', $checked);
        echo '<label for="studentpay">Students must provide an access code or payment for assessments</label></span><br class="form"/>';
    }
}


/**
 * Get a user's group ID.
 *
 * @param int $userId
 * @return int The course ID.
 */
function getUserGroupId($userId): int
{
    global $DBH;

    $stm = $DBH->prepare("SELECT groupid FROM imas_users WHERE id = :userId");
    $stm->execute(array(':userId' => $userId));
    return $stm->fetch(PDO::FETCH_NUM)[0];
}


/**
 * Get a course owner's group ID.
 *
 * @param int $courseId The course ID.
 * @return int The course owner's group ID.
 */
function getCourseOwnerGroupId($courseId): int
{
    global $DBH;

    $stm = $DBH->prepare("SELECT u.groupid
                            FROM imas_courses AS c
                                JOIN imas_users AS u ON c.ownerid = u.id
                            WHERE c.id = :courseId");
    $stm->execute(array(':courseId' => $courseId));
    return $stm->fetch(PDO::FETCH_NUM)[0];
}


/**
 * Get the current student payment / access type from the student payment API for a group.
 *
 * As of 2019 October 4, always check luministration api for group payment status
 *
 * As of 2018 July 19, valid access types are:
 * - "not_required"
 * - "activation_code"
 * - "direct_pay"
 * - "multipay"
 *
 * @param $groupId integer The group ID to get the payment/access type for.
 * @return string|null The access type. Null is returned on API communication failure.
 */
function getGroupAssessmentAccessType($groupId): ?string
{
    $groupId = Sanitize::onlyInt($groupId);

    $currentAccessType = null;
    try {
        $studentPaymentApi = new StudentPaymentApi($groupId, null, null);
        $apiResult = $studentPaymentApi->getGroupAccessType();

        // If the student payment API doesn't know about this group, then
        // there is no required access type. AKA: free assessments!
        $currentAccessType = is_null($apiResult->getAccessType()) ?
            StudentPayApiResult::ACCESS_TYPE_NOT_REQUIRED :
            $apiResult->getAccessType();
    } catch (StudentPaymentException $e) {
        // Don't allow failed API communication to break UX.
        error_log(sprintf("Exception while attempting to get student payment / access type for group ID %d: %s",
            Sanitize::onlyInt($groupId), $e->getMessage()));
        error_log($e->getTraceAsString());
    }

    return $currentAccessType;
}

