<?php
//2013 MyOpenMath

require("../validate.php");

$query = "SELECT id,custominfo FROM imas_students WHERE userid='$userid' AND courseid='".$sessiondata['paypromptcourse']."'";
$result = mysql_query($query) or die("Query failed : " . mysql_error());
$row = mysql_fetch_row($result);
$stuid = $row[0];
$custominfo = unserialize($row[1]);

if (isset($_GET['cancel'])) {
	$custominfo['payprompttime'] = 2000000000;
	$custominfo['cancelled'] = 1;
	$ci = addslashes(serialize($custominfo));
	
	$query = "UPDATE imas_students SET custominfo='$ci' WHERE id='$stuid'";
	mysql_query($query) or die("Query failed : " . mysql_error());
	
	header('Location: ' . $urlmode  . $_SERVER['HTTP_HOST'] .$imasroot . '/course/course.php?cid='.$sessiondata['paypromptcourse']);
	exit;
} else if (isset($_GET['done'])) {
	$custominfo['payprompttime'] = 2000000000;
	$custominfo['paid'] = 1;
	$ci = addslashes(serialize($custominfo));
	
	$query = "UPDATE imas_students SET custominfo='$ci',stutype=1 WHERE id='$stuid'";
	mysql_query($query) or die("Query failed : " . mysql_error());
	
	echo '<html><body><div style="text-align:center"><h1>Thanks so much!</h1><p>Have a great rest of your term!</p><p><a href="../course/course.php?cid='.$sessiondata['paypromptcourse'].'">Back to your course</a></p></div></body></html>';
	exit;
} else if (isset($_GET['later'])) {
	$custominfo['payprompttime'] += 7*24*60*60;
	if (isset($custominfo['paypromptn'])) {
		$custominfo['paypromptn']++;
	} else {
		$custominfo['paypromptn'] = 1;
	}
	$ci = addslashes(serialize($custominfo));
	$query = "UPDATE imas_students SET custominfo='$ci' WHERE id='$stuid'";
	mysql_query($query) or die("Query failed : " . mysql_error());
} else if (isset($_GET['never'])) {
	$custominfo['payprompttime'] = 2000000000;
	$custominfo['paydenied'] = 1;
	$ci = addslashes(serialize($custominfo));
	$query = "UPDATE imas_students SET custominfo='$ci' WHERE id='$stuid'";
	mysql_query($query) or die("Query failed : " . mysql_error());
} else if (isset($_GET['click'])) {
	$custominfo['payclickthrough'] = 1;
	$custominfo['paylevel'] = $_GET['v'];
	$ci = addslashes(serialize($custominfo));
	$query = "UPDATE imas_students SET custominfo='$ci' WHERE id='$stuid'";
	mysql_query($query) or die("Query failed : " . mysql_error());
}
