<?php

// Add OHM-specific columns to track student payment status.
$DBH->beginTransaction();

$query = "ALTER TABLE  `imas_groups` ADD `student_pay_enabled` TINYINT(1) NOT NULL DEFAULT '0';";
$result = $DBH->query($query);
if (false === $result) {
	echo "<p>Query failed: \"$query\". Reason: " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
}

$query = "ALTER TABLE  `imas_courses` ADD `student_pay_required` TINYINT(1) DEFAULT NULL;";
$result = $DBH->query($query);
if (false === $result) {
	echo "<p>Query failed: \"$query\". Reason: " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
}

$query = "ALTER TABLE  `imas_students` ADD `has_valid_access_code` TINYINT(1) DEFAULT NULL;";
$result = $DBH->query($query);
if (false === $result) {
	echo "<p>Query failed: \"$query\". Reason: " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
}

if ($DBH->inTransaction()) { $DBH->commit(); }

echo "<p style='color: green;'>✓ Added OHM-specific columns for student pay tracking</p>";

return true;
