<?php

/**
 * Called when copying a course via LTI.
 *
 * @param int $courseId The newly created course ID.
 * @param int $userId The course owner's user ID.
 * @param int $myrights The course owner's rights.
 * @param int $groupid The course owner's group ID.
 * @throws \OHM\Exceptions\StudentPaymentException
 */
function onCopyCourse($courseId, $userId, $myrights, $groupid): void
{
    require_once(__DIR__ . "/../../ohm/includes/StudentPaymentDb.php");

    global $DBH;

    $studentPaymentDb = new \OHM\Includes\StudentPaymentDb(null, $courseId, null, $groupid, $userId);
    $studentPaymentDb->setDbh($DBH);

    $groupRequiresStudentPayment = $studentPaymentDb->getGroupRequiresStudentPayment();
    if ($groupRequiresStudentPayment) {
        $studentPaymentDb->setCourseRequiresStudentPayment(true);
    }
}