<?php

// Add columns to user table for tracking EULA acceptance.
$DBH->beginTransaction();

$query = "ALTER TABLE `imas_users` 
  ADD COLUMN `eula_version_accepted` INT NOT NULL DEFAULT '0' AFTER `lastemail`,
  ADD COLUMN `eula_accepted_at` TIMESTAMP NULL AFTER `eula_version_accepted`,
  ALGORITHM=INPLACE,
  LOCK=NONE;";
$res = $DBH->query($query);
if ($res===false) {
    echo "<p>Query failed: ($query) : ".print_r($DBH->errorInfo(),true)."</p>";
    $DBH->rollBack();
    return false;
}


if ($DBH->inTransaction()) { $DBH->commit(); }
echo '<p>Add user EULA acceptance tracking columns.</p>';

return true;
