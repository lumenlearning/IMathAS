<?php
/**
 * OHM: Desmos Interactive
 * Explanation steps including Desmos graph in text
 */
$DBH->beginTransaction();

$query = "CREATE TABLE `desmos_graphs` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `data` text NOT NULL,
    PRIMARY KEY (`id`)
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

echo "<p style='color: green;'>✓ Created table: desmos_steps</p>";

return true;
