<?php

// Add lumen_guid column to imas_groups.
$DBH->beginTransaction();

$query = "ALTER TABLE `imas_dbschema` CHANGE COLUMN `ver` `ver` FLOAT UNSIGNED NOT NULL";
$result = $DBH->query($query);
if (false === $result) {
	echo "<p>Query failed: \"$query\". Reason: " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
}

if ($DBH->inTransaction()) { $DBH->commit(); }

echo "<p style='color: green;'>✓ Change <b>imas_dbschema.ver</b> column to FLOAT type.</p>";

return true;
