<?php

// Add a column to the student enrollments table for tracking opt-out status.
// Details in Jira: OHM-1075.
$DBH->beginTransaction();

$query = "ALTER TABLE `imas_students` 
  ADD COLUMN `is_opted_out_assessments` INT NOT NULL DEFAULT '0' AFTER `has_valid_access_code`,
  ALGORITHM=INPLACE,
  LOCK=NONE;";
$res = $DBH->query($query);
if ($res===false) {
    echo "<p>Query failed: ($query) : ".print_r($DBH->errorInfo(),true)."</p>";
    $DBH->rollBack();
    return false;
}


if ($DBH->inTransaction()) { $DBH->commit(); }
echo '<p>Add assessment opt-out tracking columns.</p>';

return true;
