<?php

$DBH->beginTransaction();

$query = "CREATE TABLE `ohm_lms_grade_reports` (
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `userid` INT(10) UNSIGNED NOT NULL,
            `assessmentid` INT(10) UNSIGNED NOT NULL,
            `ohm_grade` INT NULL DEFAULT NULL,
            `lms_grade` INT NULL DEFAULT NULL,
            `last_updated` TIMESTAMP NULL DEFAULT NULL,
            `update_pending` TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            INDEX `fk_userid_idx` (`userid` ASC),
            INDEX `fk_assessmentid_idx` (`assessmentid` ASC),
            INDEX `userid_assessmentid_idx` (`userid` ASC, `assessmentid` ASC),
            CONSTRAINT `fk_userid`
              FOREIGN KEY (`userid`)
              REFERENCES `imas_users` (`id`)
              ON DELETE CASCADE
              ON UPDATE CASCADE,
            CONSTRAINT `fk_assessmentid`
              FOREIGN KEY (`assessmentid`)
              REFERENCES `imas_assessments` (`id`)
              ON DELETE CASCADE
              ON UPDATE CASCADE);
";
$res = $DBH->query($query);
if ($res === false) {
	echo "<p>Query failed: ($query) : " . $DBH->errorInfo() . "</p>";
	$DBH->rollBack();
	return false;
}

if ($DBH->inTransaction()) { $DBH->commit(); }

echo "<p style='color: green;'>✓ Created table: ohm_lms_grade_reports</p>";

return true;
