<?php

require_once(__DIR__ . "/../../ohm/includes/StudentPaymentDb.php");


/**
 * Called when adding a course.
 *
 * @param int $courseId The course's ID.
 * @param int $userId The user's ID.
 * @throws \OHM\Exceptions\StudentPaymentException
 */
function onAddCourse($courseId, $userId)
{
    global $DBH;

    $studentPaymentDb = new \OHM\Includes\StudentPaymentDb(null, null, $userId);
    $groupRequiresStudentPayment = $studentPaymentDb->getGroupRequiresStudentPayment();

    if ($groupRequiresStudentPayment) {
        $stm = $DBH->prepare("UPDATE imas_courses SET student_pay_required = 1 WHERE id = :courseId");
        $stm->execute(array(':courseId' => $courseId));
    }
}
