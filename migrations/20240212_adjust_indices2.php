<?php

//change 
$DBH->beginTransaction();


 $query = "ALTER TABLE  `imas_content_track` DROP INDEX `courseid`, DROP INDEX `userid`,
   ADD INDEX `course_user` ( `courseid`, `userid` )";
//   ADD INDEX `type` (`type`)"; OHM already has this, see file: 20200129_add_imas_content_track_indexes.php
 $res = $DBH->query($query);
 if ($res===false) {
 	 echo "<p>Query failed: ($query) : " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
 }

 
if ($DBH->inTransaction()) { $DBH->commit(); }

echo "<p style='color: green;'>✓ Adjusted indexes imas_content_track</p>";

return true;
