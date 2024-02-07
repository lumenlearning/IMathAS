<?php
/**
 * OHM: Desmos Interactive
 * Add columns for tracking item parent/child chains
 */
$DBH->beginTransaction();

$query = "ALTER TABLE `desmos_items` 
    ADD COLUMN `origin_itemid` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `steporder`,
    ADD COLUMN `itemid_chain` TEXT NULL DEFAULT NULL AFTER `origin_itemid`,
    ADD COLUMN `itemid_chain_size` INT(10) NULL DEFAULT NULL AFTER `itemid_chain`,
    ADD INDEX `origin_itemid` (`origin_itemid` ASC),
    ADD INDEX `itemid_chain_size` (`itemid_chain_size` ASC)
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
