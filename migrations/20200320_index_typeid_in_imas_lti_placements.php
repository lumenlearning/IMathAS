<?php
/**
 * OHM: imas_lti_placements
 * Add index on typeid column
 */
$DBH->beginTransaction();

$query = "ALTER TABLE `imas_lti_placements`
    ADD INDEX `typeid` (`typeid` ASC),
      ALGORITHM=INPLACE,
      LOCK=NONE
";
$res = $DBH->query($query);
if ($res === false) {
    echo "<p>Query failed: ($query) : ";
    var_dump($DBH->errorInfo());
    echo  "</p>";
    $DBH->rollBack();
    return false;
}

if ($DBH->inTransaction()) { $DBH->commit(); }

echo "<p style='color: green;'>✓ Added index on typeid columns to table: imas_lti_placements</p>";

return true;
