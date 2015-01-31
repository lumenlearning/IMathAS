<?php 

require("../config.php");

$now = time();

$query = "INSERT INTO imas_pings (time) VALUES ($now)";
mysql_query($query) or die("Query failed : " . mysql_error());

?>
<html>
<body>
Pong.
</body>
</html>
