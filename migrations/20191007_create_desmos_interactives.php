<?php


$DBH->beginTransaction();

$query = "CREATE TABLE `desmos_interactives` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `courseid` int(10) unsigned NOT NULL,
            `title` varchar(254) NOT NULL,
            `summary` text NOT NULL,
            `startdate` int(10) unsigned NOT NULL,
            `enddate` int(10) unsigned NOT NULL,
            `avail` tinyint(1) unsigned NOT NULL DEFAULT '1',
            `outcomes` text NOT NULL,
            `libs` VARCHAR(255) NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
              KEY `courseid` (`courseid`),
              KEY `avail` (`avail`),
              KEY `startdate` (`startdate`),
              KEY `enddate` (`enddate`),
		    INDEX (`courseid`)
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

$DBH->commit();

echo "<p style='color: green;'>✓ Created table: desmos_interactives</p>";

return true;
