<?php
/**
 * Skip index generation if they already exist.
 */

$stm = $DBH->prepare('SHOW INDEX FROM `imas_content_track`');
$stm->execute();

$skip = false;
while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
    if ('type' == $row['Key_name']) {
        $skip = true;
    }
}

if (true === $skip) {
    echo "<p style='color: green;'>✓ Skipping index creation for table: imas_content_track (already exists)</p>";
    return true;
}


/**
 * Add indexes to table: imas_content_track (type, viewtime)
 */

$DBH->beginTransaction();

$query = "ALTER TABLE `imas_content_track` 
    ADD INDEX `type` (`type` ASC),
    ADD INDEX `viewtime` (`viewtime` ASC),
    ALGORITHM=INPLACE,
    LOCK=NONE";

$res = $DBH->query($query);
if ($res === false) {
    echo "<p>Query failed: ($query) : ";
    var_dump($DBH->errorInfo());
    echo  "</p>";
    $DBH->rollBack();
    return false;
}

if ($DBH->inTransaction()) { $DBH->commit(); }

echo "<p style='color: green;'>✓ Adding indexes to table: imas_content_track (type, viewtime)</p>";
return true;
