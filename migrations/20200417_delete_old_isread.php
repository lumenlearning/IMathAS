<?php
/**
 * OHM-specific changes: Don't DROP anything.
 * OHM-594: Make the MOM changes not have any downtime
 */

$DBH->beginTransaction();

  // update any values that were missed
  $query = "UPDATE imas_msgs SET
    viewed = (isread&1),
    deleted = ROUND((isread&4)/4 + (isread&2)),
    tagged = ((isread&8)/8)
    WHERE isread>0 AND viewed=0 AND deleted=0 AND tagged=0";
  $res = $DBH->query($query);
  if ($res===false) {
     echo "<p>Query failed: ($query) : " . $DBH->errorInfo() . "</p>";
  $DBH->rollBack();
  return false;
  }

  // OHM-594: Commented this out to allow for rollbacks.
  // drop old columns
  $query = "ALTER TABLE `imas_msgs` DROP COLUMN isread, DROP INDEX msgfrom";
  $res = $DBH->query($query);
  if ($res===false) {
    echo "<p>Query failed: ($query) : " . $DBH->errorInfo() . "</p>";
  $DBH->rollBack();
  return false;
  }

$DBH->commit();

echo "<p style='color: green;'>✓ Updated imas_msgs.{viewed,deleted,tagged} columns</p>";

return true;
