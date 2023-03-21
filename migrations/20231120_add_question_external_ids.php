<?php

// Add a column to imas_questionset for external question IDs.
// Details in Jira: OHM-1150
$DBH->beginTransaction();

// ALGORITHM=INPLACE is not supported on this table.
$query = "ALTER TABLE `imas_questionset` 
  ADD COLUMN `external_id` VARCHAR(36) NULL;";
$res = $DBH->query($query);
if ($res===false) {
    echo "<p>Query failed: ($query) : ".print_r($DBH->errorInfo(),true)."</p>";
    $DBH->rollBack();
    return false;
}


$DBH->commit();
echo '<p>Add external_id columnn to imas_questionset.</p>';

return true;
