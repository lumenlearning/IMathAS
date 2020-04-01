<?php


//Add imas_teacher_audit_log table
$DBH->beginTransaction();

$query = 'ALTER TABLE `imas_teacher_audit_log` 
CHANGE COLUMN `action` `action` ENUM(\'Assessment Settings Change\', \'Mass Assessment Settings Change\', \'Mass Date Change\', \'Question Settings Change\', \'Clear Attempts\', \'Clear Scores\', \'Delete Item\', \'Unenroll\', \'Change Grades\') NULL DEFAULT NULL ;';
$res = $DBH->query($query);
if ($res===false) {
    echo "<p>Query failed: ($query) : ".$DBH->errorInfo()."</p>";
    $DBH->rollBack();
    return false;
}
$DBH->commit();
echo '<p>table imas_teacher_audit_log action field enum updated: 
\'Assessment Settings Change\', \'Mass Assessment Settings Change\', \'Mass Date Change\', \'Question Settings Change\', \'Clear Attempts\', \'Clear Scores\', \'Delete Item\', \'Unenroll\', \'Change Grades\'</p>';

return true;
