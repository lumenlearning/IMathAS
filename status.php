<?php
require_once("init_without_validate.php");
$DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$DBH->query("SELECT id FROM imas_users WHERE 1 LIMIT 1");
echo 'Success';
?>
