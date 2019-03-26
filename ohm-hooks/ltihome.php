<?php


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
