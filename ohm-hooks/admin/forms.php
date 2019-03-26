<?php

use OHM\Models\StudentPayApiResult;
use OHM\Exceptions\StudentPaymentException;


function getHeaderCode()
{
    global $imasroot;

    return '<script type="text/javascript" src="' . $imasroot . '/ohm/js/student_pay/studentPayAjax.js"></script>';

}


/**
 * Render a form snippet for the "create/modify a course" page.
 *
 * @param string $action
 * @param int $myrights
 * @param int $courseId
 * @throws StudentPaymentException
 */
function getCourseSettingsForm($action, $myrights, $courseId)
{
    renderCourseRequiresStudentPayment($action);
}


/**
 * Render a form snippet for the "modify a group" page.
 *
 * @param $groupId
 * @param $groupType
 * @param $myrights
 */
function getModGroupForm($groupId, $groupType, $myrights)
{
    global $DBH;

    echo '<input type="checkbox" id="iscust" name="iscust" ';
    if ($groupType == 1) {
        echo 'checked';
    }
    echo '> <label for="iscust">' . _('Lumen Customer') . '</label><br/>';

    if (100 <= $myrights) {
        $stm = $DBH->prepare("SELECT lumen_guid FROM imas_groups WHERE id = :groupId");
        $stm->execute(array(':groupId' => $_GET['id']));
        $lumenGuid = $stm->fetchColumn(0);
        printf('Lumen GUID: <input type="text" name="lumen_guid" size="50" value="%s"/><br/>', $lumenGuid);

        if (isset($GLOBALS['student_pay_api']) && $GLOBALS['student_pay_api']['enabled']) {
            echo "<div id='ohmEditGroup'>";

            $currentAccessType = getGroupAssessmentAccessType($groupId);
            if (is_null($currentAccessType)) {
                echo "<div id='student_payment_api_failure'>Error: Failed to get current student payment / access type from API.</div>";
            }

            renderAccessTypeSelector($currentAccessType);

            echo '<span id="student_payment_update_message"></span>';
            printf('<br/><button id="update_student_payment_type" type="button"'
                . ' onClick="updateStudentPaymentType(%d);">Update student payment type</button>',
                Sanitize::onlyInt($groupId));

            echo "</div>";
        }
    }
}


/*
 * The following are what would normally be private methods.
 */


/**
 * Render the "Assessments require payment or activation" portion of the
 * Create/Modify Course page.
 *
 * @param string $action One of "addcourse" or "modify"
 * @throws \OHM\Exceptions\StudentPaymentException
 */
function renderCourseRequiresStudentPayment($action)
{
    extract($GLOBALS, EXTR_SKIP | EXTR_REFS); // Sadface. :(

    if (100 <= $GLOBALS['myrights'] && isset($GLOBALS['student_pay_api']) && $GLOBALS['student_pay_api']['enabled']) {
        require_once(__DIR__ . "/../../ohm/includes/StudentPaymentDb.php");

        $courseId = intval($_GET['id']);

        $courseOwnerGroupId = null;
        if ('addcourse' == $action) {
            $stm = $GLOBALS['DBH']->prepare("SELECT g.id
												FROM imas_users AS u
													JOIN imas_groups AS g ON g.id = u.groupid
													WHERE u.id = :userId");
            $stm->execute(array(':userId' => $GLOBALS['userid']));
            $courseOwnerGroupId = $stm->fetch(PDO::FETCH_NUM)[0];
        }
        if ('modify' == $action) {
            $stm = $GLOBALS['DBH']->prepare("SELECT u.groupid
												FROM imas_courses AS c
													JOIN imas_users AS u ON c.ownerid = u.id
													WHERE c.id = :courseId");
            $stm->execute(array(':courseId' => $courseId));
            $courseOwnerGroupId = $stm->fetch(PDO::FETCH_NUM)[0];
        }

        if (empty($courseOwnerGroupId)) {
            // It's possible for users to have a group ID of "0". (default group)
            // Group 0 isn't an actual group, so we can't do anything with that.
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
}


/**
 * Get the current student payment / access type from the student payment API for a group.
 *
 * If our cache (OHM db) says student payments are disabled for a group, we
 * immediately return "not_required".
 *
 * As of 2018 Apr 2, valid access types are:
 * - "not_required"
 * - "activation_code"
 * - "direct_pay"
 *
 * @param $groupId integer The group ID to get the payment/access type for.
 * @return string The access type. Null is returned on API communication failure.
 */
function getGroupAssessmentAccessType($groupId)
{
    require_once(__DIR__ . "/../../ohm/includes/StudentPaymentDb.php");
    require_once(__DIR__ . "/../../ohm/models/StudentPayApiResult.php");

    $groupId = Sanitize::onlyInt($groupId);
    $studentPaymentDb = new \OHM\Includes\StudentPaymentDb($groupId, null, null);

    $currentAccessType = null;
    try {
        if ($studentPaymentDb->getGroupRequiresStudentPayment()) {
            require_once(__DIR__ . "/../../ohm/includes/StudentPaymentApi.php");
            $studentPaymentApi = new \OHM\Includes\StudentPaymentApi($groupId, null, null);
            $apiResult = $studentPaymentApi->getGroupAccessType();

            // If the student payment API doesn't know about this group, then
            // there is no required access type. AKA: free assessments!
            $currentAccessType = is_null($apiResult->getAccessType()) ?
                StudentPayApiResult::ACCESS_TYPE_NOT_REQUIRED :
                $apiResult->getAccessType();
        } else {
            $currentAccessType = \OHM\Models\StudentPayApiResult::ACCESS_TYPE_NOT_REQUIRED;
        }
    } catch (StudentPaymentException $e) {
        // Don't allow failed API communication to break UX.
        error_log(sprintf("Exception while attempting to get student payment / access type for group ID %d: %s",
            Sanitize::onlyInt($groupId), $e->getMessage()));
        error_log($e->getTraceAsString());
    }

    return $currentAccessType;
}


/**
 * Render the <select> portion of a form for student payment / access types.
 *
 * @param $currentAccessType string The groups current student payment / access type.
 */
function renderAccessTypeSelector($currentAccessType)
{
    $validAccessTypes = array(
        'not_required' => 'Not required',
        'direct_pay' => 'Direct Pay - Student pays directly',
        'activation_code' => 'Activation codes - Student enters an access code',
        'multipay' => 'Multipay - Both methods'
    );

    echo "<label for='student_payment_type'>Student payments:</label>";
    echo "<select id='student_payment_type' name='student_payment_type' aria-label='Student payments'>";
    foreach ($validAccessTypes as $key => $value) {
        $selected = $currentAccessType == $key ? " selected='selected'" : "";
        printf("<option value='%s'%s>%s</option>", $key, $selected, $value);
    }
    echo "</select>";
}

