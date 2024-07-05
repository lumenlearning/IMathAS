<?php
/**
 * OHM: Desmos Interactive
 * Change step column text to longblob
 */
$DBH->beginTransaction();

$query = "ALTER TABLE `desmos_steps` 
    CHANGE COLUMN `text` `text` LONGBLOB NULL DEFAULT NULL
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

echo "<p style='color: green;'>✓ Added desmos item history columns to table: desmos_items</p>";

return true;
