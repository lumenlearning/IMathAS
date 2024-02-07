<?php
/**
 * Add a created_at column to the desmos_items table.
 */

$DBH->beginTransaction();

$query = "ALTER TABLE `desmos_items` 
    ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() AFTER `itemid_chain_size`";

$res = $DBH->query($query);
if ($res === false) {
    echo "<p>Query failed: ($query) : ";
    var_dump($DBH->errorInfo());
    echo  "</p>";
    $DBH->rollBack();
    return false;
}

if ($DBH->inTransaction()) { $DBH->commit(); }

echo "<p style='color: green;'>✓ Added 'created_at' column to table: desmos_items</p>";
return true;
