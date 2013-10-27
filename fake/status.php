<?php
require_once("config.php");
$query = "SELECT count(*) FROM imas_fakeusers";
$result = mysql_query($query) or exit(500);
echo 'Success';
?>