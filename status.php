<?php
require_once("config.php");
$DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$DBH->query("SELECT count(*) FROM imas_users");
echo 'Success';
?>
