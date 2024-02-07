<?php
/**
 * OHM: Desmos Interactive
 * Explanation steps including Desmos graph in text
 */
$DBH->beginTransaction();

$query = "CREATE TABLE `desmos_steps` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `desmosid` int(10) unsigned NOT NULL,
    `title` varchar(254) NOT NULL,
    `text` text NOT NULL,
    PRIMARY KEY (`id`),
      KEY `desmosid` (`desmosid`),
    INDEX (`desmosid`)
    ) ENGINE=InnoDB;
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

echo "<p style='color: green;'>✓ Created table: desmos_steps</p>";

return true;
