<?php


use OHM\Exceptions\StudentPaymentException;

function onModCourse($id, $userid, $myrights, $groupid)
{
    global $DBH;

    // Update the "student payments required" course setting.
    if (100 <= $myrights && isset($GLOBALS['student_pay_api']) && $GLOBALS['student_pay_api']['enabled']) {
        $query = "UPDATE imas_courses SET student_pay_required = :studentpay WHERE id = :id";

        $qarr = array(':id' => $id);
        $qarr[':studentpay'] = $_POST['studentpay'] ? 1 : 0;

        $stm = $DBH->prepare($query);
        $stm->execute($qarr);
    }
}


function onAddCourse($cid, $userid, $myrights, $groupid)
{
    require_once(__DIR__ . "/../../ohm/includes/StudentPaymentDb.php");

    global $DBH;

    $studentPaymentDb = new \OHM\Includes\StudentPaymentDb(null, $cid, $userid);
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


function onModGroup($id, $userid, $myrights, $groupid)
{
    global $DBH;

    if (100 <= $myrights) {
        $stm = $DBH->prepare("UPDATE imas_groups SET lumen_guid = :lumenGuid WHERE id = :groupId");
        // TODO: Test this!
        // The original code used $_GET['id'] for the :groupId
        $stm->execute(array(':lumenGuid' => $_POST['lumen_guid'], ':groupId' => $id));
    }

}
