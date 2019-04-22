<?php


/**
 * Called when creating a course via LTI.
 *
 * $myrights and $groupid are optional because there is a second call to this
 * function in bltilaunch.php that excludes those arguments. This is okay for
 * now since they're currently unused in this hook.
 *
 * @param int $courseId The course ID.
 * @param int $userId The user's ID.
 * @param int|null $myrights The user's rights.
 * @param int|null $groupid The user's group ID.
 * @throws \OHM\Exceptions\StudentPaymentException
 */
function onAddCourse($courseId, $userId, $myrights = null, $groupid = null)
{
    require_once(__DIR__ . "/../ohm/includes/StudentPaymentDb.php");

    global $DBH;

    $studentPaymentDb = new \OHM\Includes\StudentPaymentDb(null, $courseId, $userId);
    $studentPaymentDb->setDbh($DBH);

    $groupRequiresStudentPayment = $studentPaymentDb->getGroupRequiresStudentPayment();
    if ($groupRequiresStudentPayment) {
        $studentPaymentDb->setCourseRequiresStudentPayment(true);
    }
}
