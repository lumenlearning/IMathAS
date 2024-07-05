<?php

// Add two columns to imas_ltiqueue to record user ID and assessment ID
// where OHM-specific code is adding rows to this table.
// Details in Jira: OHM-1096, OHM-1090, OHM-1092, OHM-1095, OHM-1096.
$DBH->beginTransaction();

$query = "ALTER TABLE `imas_ltiqueue` 
  ADD COLUMN `userid` INT NULL AFTER `sendon`,
  ADD COLUMN `assessmentid` INT NULL AFTER `userid`,
  ALGORITHM=INPLACE,
  LOCK=NONE;";
$res = $DBH->query($query);
if ($res===false) {
    echo "<p>Query failed: ($query) : ".print_r($DBH->errorInfo(),true)."</p>";
    $DBH->rollBack();
    return false;
}


if ($DBH->inTransaction()) { $DBH->commit(); }
echo '<p>Add userid and assessmentid columnns to imas_ltiqueue.</p>';

return true;
