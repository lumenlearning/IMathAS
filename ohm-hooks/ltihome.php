<?php


/**
 * Called when adding a course via LTI.
 *
 * @param int $courseId The course's ID.
 * @param int $userId The user's ID.
 * @throws \OHM\Exceptions\StudentPaymentException
 */
function onAddCourse($courseId, $userId)
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
