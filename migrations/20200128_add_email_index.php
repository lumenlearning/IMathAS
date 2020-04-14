<?php

//Add additional indexes
$DBH->beginTransaction();

 // OHM-specific change: Make this a non-blocking operation. (algorithm=inplace)
 $query = "ALTER TABLE  `imas_users` ADD INDEX ( `email` ), ALGORITHM=INPLACE, LOCK=NONE";
 $res = $DBH->query($query);
 if ($res===false) {
 	 echo "<p>Query failed: ($query) : " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
 }

$DBH->commit();

echo "<p style='color: green;'>✓ Added users email index</p>";

return true;
