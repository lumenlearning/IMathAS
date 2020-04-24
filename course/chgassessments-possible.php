<?php
//chgassessments.php near line 340
$stm = $DBH->query("SELECT * FROM imas_assessments WHERE id IN ($checkedlist)");
$stmupd = $DBH->prepare("UPDATE imas_assessments SET $setslist WHERE id=:id");
while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
    $rowqarr = $qarr;
    $rowqarr[':id'] = $row['id'];
    if (isset($_POST['chgintro'])) {
        $chgstm = $DBH->prepare("SELECT intro FROM imas_assessments WHERE id=:id");
        $chgstm->execute(array(':id'=>Sanitize::onlyInt($_POST['intro'])));
        $cpintro = $chgstm->fetchColumn(0);
        if (($introjson=json_decode($cpintro))!==null) { //is json intro
            $newintro = $introjson[0];
        } else {
            $newintro = $cpintro;
        }
        if (($introjson=json_decode($row['intro']))!==null) { //is json intro
            $introjson[0] = $newintro;
            $outintro = json_encode($introjson, JSON_INVALID_UTF8_IGNORE);
        } else {
            $outintro = $newintro;
        }
        $rowqarr[':intro']=$outintro;
    }
    $stmupd->execute($rowqarr);
    if ($stm->rowCount()>0) {
        //remove : from key of rowqarr
        //find $rowqarr that are different than $row values
        $result = TeacherAuditLog::addTracking(
            $cid,
            "Mass Assessment Settings Change",
            null,
            $rowqarr
        );
    }
}