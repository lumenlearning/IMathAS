<?php
/**
 * Add a table for tracking notice dismissals.
 */
$DBH->beginTransaction();

$query = "CREATE TABLE `ohm_notice_dismissals` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `userid` INT NOT NULL,
  `noticeid` INT NOT NULL,
  `dismissed_at` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `userid_noticeid_idx` (`userid` ASC, `noticeid` ASC));
";

$res = $DBH->query($query);
if ($res === false) {
    echo "<p>Query failed: ($query) : ";
    var_dump($DBH->errorInfo());
    echo "</p>";
    $DBH->rollBack();
    return false;
}

if ($DBH->inTransaction()) { $DBH->commit(); }

echo "<p style='color: green;'>✓ Added table: ohm_notice_dismissals</p>";

return true;
