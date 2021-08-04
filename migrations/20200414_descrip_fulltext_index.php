<?php
/**
 * OHM-specific changes: Create index in non-blocking mode.
 * OHM-594: Make the MOM changes not have any downtime
 */

//Add additional indexes
$DBH->beginTransaction();

 $query = "ALTER TABLE `imas_questionset`
    ADD FULLTEXT INDEX `descidx` (`description`),
    ALGORITHM=INPLACE,
    LOCK=SHARED";
 $res = $DBH->query($query);
 if ($res===false) {
 	 echo "<p>Query failed: ($query) : " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
 }


if ($DBH->inTransaction()) { $DBH->commit(); }

echo "<p style='color: green;'>✓ Added fulltext index on questionset description</p>";

return true;
