<?php


use OHM\Exceptions\StudentPaymentException;

/**
 * Called when a modifying a course, on form submission.
 *
 * @param int $courseId The course's ID. (from imas_courses)
 * @param int $userId The user's ID.
 * @param int $userRights The user's rights. (from imas_users)
 * @param int $userGroupId The user's group ID.
 */
function onModCourse($courseId, $userId, $userRights, $userGroupId)
{
    global $DBH;

    // Update the "student payments required" course setting.
    if (100 <= $userRights && isset($GLOBALS['student_pay_api']) && $GLOBALS['student_pay_api']['enabled']) {
        $query = "UPDATE imas_courses SET student_pay_required = :studentpay WHERE id = :courseId";

        $qarr = array(':courseId' => $courseId);
        $qarr[':studentpay'] = $_POST['studentpay'] ? 1 : 0;

        $stm = $DBH->prepare($query);
        $stm->execute($qarr);
    }
}


/**
 * Called when creating a new course, on form submission.
 *
 * @param int $courseId The course's ID. (from imas_courses)
 * @param int $userId The user's ID.
 * @param int $userRights The user's rights. (from imas_users)
 * @param int $userGroupId The user's group ID.
 */
function onAddCourse($courseId, $userId, $userRights, $userGroupId)
{
    require_once(__DIR__ . "/../../ohm/includes/StudentPaymentDb.php");

    global $DBH;

    $studentPaymentDb = new \OHM\Includes\StudentPaymentDb(null, $courseId, $userId);
    $studentPaymentDb->setDbh($DBH);

    // If payments are enabled at the group level, enable them at the course level.
    try {
        $groupRequiresStudentPayment = $studentPaymentDb->getGroupRequiresStudentPayment();
        if ($groupRequiresStudentPayment) {
            $studentPaymentDb->setCourseRequiresStudentPayment(true);
        }
    } catch (StudentPaymentException $e) {
        error_log($e->getMessage());
        error_log($e->getTraceAsString());
    }
}


/**
 * Called when modifying a group, on form submission.
 *
 * @param int $groupId The group's ID.
 * @param int $userId The user's ID.
 * @param int $myrights The user's rights. (from imas_users)
 * @param int $userGroupId The user's group ID.
 */
function onModGroup($groupId, $userId, $myrights, $userGroupId)
{
    global $DBH;

    if (100 <= $myrights) {
        $stm = $DBH->prepare("UPDATE imas_groups SET lumen_guid = :lumenGuid WHERE id = :groupId");
        // TODO: Test this!
        // The original code used $_GET['id'] for the :groupId
        $stm->execute(array(
            ':lumenGuid' => Sanitize::simpleString($_POST['lumen_guid']),
            ':groupId' => $groupId
        ));
    }

}
