<?php
/**
 * OHM-specific changes: Create indexes in non-blocking mode.
 * OHM-594: Make the MOM changes not have any downtime
 */

//Add better meantime, meanscore columns
$DBH->beginTransaction();

 $query = "ALTER TABLE `imas_msgs`
  ADD COLUMN `deleted` TINYINT(1) NOT NULL DEFAULT '0',
  ADD COLUMN `tagged` TINYINT(1) NOT NULL DEFAULT '0',
  ADD COLUMN `viewed` TINYINT(1) NOT NULL DEFAULT '0',
  ALGORITHM=INPLACE,
  LOCK=NONE";
 $res = $DBH->query($query);
 if ($res===false) {
    echo "<p>Query failed: ($query) : " . $DBH->errorInfo() . "</p>";
 $DBH->rollBack();
 return false;
 }

 $query = "UPDATE imas_msgs SET
   viewed = (isread&1),
   deleted = ROUND((isread&4)/4 + (isread&2)),
   tagged = ((isread&8)/8)
   WHERE isread>0";
 $res = $DBH->query($query);
 if ($res===false) {
    echo "<p>Query failed: ($query) : " . $DBH->errorInfo() . "</p>";
 $DBH->rollBack();
 return false;
 }

 // most common query is to get unread messages, so need combo index for that
 $query = "ALTER TABLE `imas_msgs`
    ADD INDEX `tocombo` (`msgto`, `viewed`, `courseid`),
    ALGORITHM=INPLACE,
    LOCK=NONE";
 $res = $DBH->query($query);
 if ($res===false) {
 	 echo "<p>Query failed: ($query) : " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
 }
 $query = "ALTER TABLE `imas_msgs`
    ADD INDEX `fromcombo` (`msgfrom`, `deleted`, `courseid`),
    ALGORITHM=INPLACE,
    LOCK=NONE";
 $res = $DBH->query($query);
 if ($res===false) {
 	 echo "<p>Query failed: ($query) : " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
 }
 $query = "ALTER TABLE `imas_msgs`
    ADD INDEX `tagged` (`tagged`),
    ALGORITHM=INPLACE,
    LOCK=NONE";
 $res = $DBH->query($query);
 if ($res===false) {
    echo "<p>Query failed: ($query) : " . $DBH->errorInfo() . "</p>";
 $DBH->rollBack();
 return false;
 }
 $query = "ALTER TABLE `imas_msgs`
    ADD INDEX `deleted` (`deleted`),
    ALGORITHM=INPLACE,
    LOCK=NONE";
 $res = $DBH->query($query);
 if ($res===false) {
    echo "<p>Query failed: ($query) : " . $DBH->errorInfo() . "</p>";
 $DBH->rollBack();
 return false;
 }

if ($DBH->inTransaction()) { $DBH->commit(); }

echo "<p style='color: green;'>✓ Added new imas_msgs columns to break up isread</p>";

return true;
