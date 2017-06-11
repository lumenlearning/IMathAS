<?php

require("../init_without_validate.php");

$now = time();

//DB $query = "INSERT INTO imas_pings (time) VALUES ($now)";
//DB mysql_query($query) or die("Query failed : " . mysql_error());
$stm = $DBH->prepare("INSERT INTO imas_pings (time) VALUES (:now)");
$stm->execute(array(':now'=>$now));

?>
<html>
<body>
Pong.
</body>
</html>
