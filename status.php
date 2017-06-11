<?php
require_once("init_without_validate.php");
$DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$DBH->query("SELECT count(*) FROM imas_users");
echo 'Success';
?>
