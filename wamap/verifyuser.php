<?php
require("../config.php");
if ($_GET['secret']=='572ab2') {
	$query = "SELECT FirstName,LastName,email FROM imas_users WHERE SID='{$_GET['a']}' AND password='{$_GET['b']}'";
	$result = mysql_query($query) or die("Query failed : $query" . mysql_error());
	if (mysql_num_rows($result)>0) {
		$wamap_user_info = mysql_fetch_row($result);
		echo implode(',', $wamap_user_info);
	} else {
		echo "false";
	}
}
?>
