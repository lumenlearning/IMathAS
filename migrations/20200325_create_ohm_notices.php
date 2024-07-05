<?php
/**
 * Create table: ohm_notices
 */
$DBH->beginTransaction();

$query = "CREATE TABLE `ohm_notices` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `is_enabled` TINYINT(1) NOT NULL,
  `is_dismissable` TINYINT(1) NOT NULL DEFAULT 1,
  `start_at` TIMESTAMP NULL,
  `end_at` TIMESTAMP NULL,
  `display_student` TINYINT(1) NOT NULL,
  `display_teacher` TINYINT(1) NOT NULL,
  `description` VARCHAR(254) NOT NULL,
  `student_title` VARCHAR(127) NULL,
  `student_content` TEXT NULL,
  `teacher_title` VARCHAR(127) NULL,
  `teacher_content` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `start_at_idx` (`start_at` ASC),
  INDEX `end_at_idx` (`end_at` ASC),
  INDEX `is_enabled_idx` (`is_enabled` ASC),
  INDEX `display_student` (`display_student` ASC),
  INDEX `display_teacher` (`display_teacher` ASC)
);
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

echo "<p style='color: green;'>✓ Created table: ohm_notices</p>";

return true;
