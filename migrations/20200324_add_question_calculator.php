<?php
/**
 * OHM: Desmos Calculator
 * Add showcalculator column imas_questions table
 */
$DBH->beginTransaction();

$query = "ALTER TABLE `imas_questions` 
    ADD COLUMN `showcalculator` ENUM('default', 'none', 'basic', 'scientific', 'graphing', 'geometry') NOT NULL DEFAULT 'default' AFTER `showans`,
    ADD INDEX `showcalculator` (`showcalculator` ASC)
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

echo "<p style='color: green;'>✓ Added showcalculator columns to table: imas_questions</p>";

return true;