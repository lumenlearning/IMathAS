<?php

// Add created_at columns.
$DBH->beginTransaction();

$query = "ALTER TABLE  `imas_students` ADD `created_at` INT(10) DEFAULT NULL";
$result = $DBH->query($query);
if (false === $result) {
	echo "<p>Query failed: \"$query\". Reason: " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
}

$query = "ALTER TABLE  `imas_teachers` ADD `created_at` INT(10) DEFAULT NULL";
$result = $DBH->query($query);
if (false === $result) {
	echo "<p>Query failed: \"$query\". Reason: " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
}

$query = "ALTER TABLE  `imas_tutors` ADD `created_at` INT(10) DEFAULT NULL";
$result = $DBH->query($query);
if (false === $result) {
	echo "<p>Query failed: \"$query\". Reason: " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
}

$query = "ALTER TABLE  `imas_users` ADD `created_at` INT(10) DEFAULT NULL";
$result = $DBH->query($query);
if (false === $result) {
	echo "<p>Query failed: \"$query\". Reason: " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
}

$query = "ALTER TABLE  `imas_groups` ADD `created_at` INT(10) DEFAULT NULL";
$result = $DBH->query($query);
if (false === $result) {
	echo "<p>Query failed: \"$query\". Reason: " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
}

$query = "ALTER TABLE  `imas_courses` ADD `created_at` INT(10) DEFAULT NULL";
$result = $DBH->query($query);
if (false === $result) {
	echo "<p>Query failed: \"$query\". Reason: " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
}

if ($DBH->inTransaction()) { $DBH->commit(); }

echo "<p style='color: green;'>✓ Added <b>created_at</b> columns.</p>";

return true;
