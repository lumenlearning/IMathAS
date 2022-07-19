<?php

require_once(__DIR__ . "/../../ohm/includes/StudentPaymentDb.php");


/**
 * Called when adding a course while adding an instructor.
 *
 * @param int $courseId The course's ID.
 * @param int $courseOwnerUserId The course owner's user ID.
 * @throws \OHM\Exceptions\StudentPaymentException
 */
function onAddCourse($courseId, $courseOwnerUserId)
{
    global $DBH;

    $studentPaymentDb = new \OHM\Includes\StudentPaymentDb(null, null, null, null, $courseOwnerUserId);
    $groupRequiresStudentPayment = $studentPaymentDb->getGroupRequiresStudentPayment();

    if ($groupRequiresStudentPayment) {
        $stm = $DBH->prepare("UPDATE imas_courses SET student_pay_required = 1 WHERE id = :courseId");
        $stm->execute(array(':courseId' => $courseId));
    }
}
