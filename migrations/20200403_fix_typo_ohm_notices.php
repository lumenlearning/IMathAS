<?php
/**
 * Create table: ohm_notices
 */
$DBH->beginTransaction();

$query = "ALTER TABLE `ohm_notices` 
CHANGE COLUMN `is_dismissable` `is_dismissible` TINYINT(1) NOT NULL DEFAULT '1' ;
";

$res = $DBH->query($query);
if ($res === false) {
    echo "<p>Query failed: ($query) : ";
    var_dump($DBH->errorInfo());
    echo  "</p>";
    $DBH->rollBack();
    return false;
}

if ($DBH->inTransaction()) { $DBH->commit(); }

echo "<p style='color: green;'>✓ Renamed column: ohm_notices.is_dismissable -&gt; is_dismissible</p>";

return true;
