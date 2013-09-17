<?php
//reporting on contributions
require("../validate.php");

if ($myrights<100) { exit;}

$query = "SELECT custominfo FROM imas_students WHERE custominfo<>''";
$result = mysql_query($query) or die("Query failed : " . mysql_error());

$denied = 0;
$cnt = 0;
$paid = array();
$paidpot = 0;
$delayed = 0;
$paidcnt = 0;
while ($row = mysql_fetch_row($result)) {
	$ci = unserialize($row[0]);
	$cnt++;
	if (isset($ci['paydenied'])) {
		$denied++;
	} else if (isset($ci['paid'])) {
		if (!isset($paid[$ci['paylevel']])) {
			$paid[$ci['paylevel']] = 1;
		} else {
			$paid[$ci['paylevel']]++;
		}
		$paidcnt++;
	} else if (isset($ci['payclickthrough'])) {
		$paidpot++;	
	} else if (isset($ci['paypromptn'])) {
		$delayed++;
	}
}

echo "<p>Total students: $cnt</p>";
echo "<p>Delayed: $delayed.  Denied: $denied.  Not completed: $paidpot</p>";
echo "<p>Paid Bronze: ".$paid['Bronze'].".</p>";
echo "<p>Paid Silver: ".$paid['Silver'].".</p>";
echo "<p>Paid Gold: ".$paid['Gold'].".</p>";
echo "<p>Paid: $paidcnt</p>";




?>
