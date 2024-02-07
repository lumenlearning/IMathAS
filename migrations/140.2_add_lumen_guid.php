<?php

// Add lumen_guid column to imas_groups.
$DBH->beginTransaction();

$query = "ALTER TABLE  `imas_groups` ADD `lumen_guid` VARCHAR(36) DEFAULT NULL";
$result = $DBH->query($query);
if (false === $result) {
	echo "<p>Query failed: \"$query\". Reason: " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
}

if ($DBH->inTransaction()) { $DBH->commit(); }

echo "<p style='color: green;'>✓ Added <b>lumen_guid</b> column to <b>imas_groups</b>.</p>";

return true;
