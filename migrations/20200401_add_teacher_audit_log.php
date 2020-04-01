<?php

//Add imas_teacher_audit_log table
$DBH->beginTransaction();

$query = 'CREATE TABLE `imas_teacher_audit_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `courseid` int(10) unsigned NOT NULL,
  `action` ENUM("Assessment Settings Change","Mass Assessment Setting Change","Mass Assessment Date Change","Question Settings Change","Clear Attempts","Clear Scores","Delete Item", "Unenroll","Grade Override"),
  `itemid` int(10) unsigned NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `metadata` json NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `courseuser` (`courseid`,`userid`),
  INDEX `actionid` (`action`, `itemid`),
  INDEX `timestamp` (`timestamp`)
) ENGINE=InnoDB;';
$res = $DBH->query($query);
if ($res===false) {
    echo "<p>Query failed: ($query) : ".$DBH->errorInfo()."</p>";
    $DBH->rollBack();
    return false;
}
echo '<p>table imas_user_prefs created</p>';

return true;

?>
