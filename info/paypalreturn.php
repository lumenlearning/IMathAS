<?php
//2013 MyOpenMath

require("../init.php");

//DB $query = "SELECT id,custominfo FROM imas_students WHERE userid='$userid' AND courseid='".$sessiondata['paypromptcourse']."'";
//DB $result = mysql_query($query) or die("Query failed : " . mysql_error());
//DB $row = mysql_fetch_row($result);
$stm = $DBH->prepare("SELECT id,custominfo FROM imas_students WHERE userid=:userid AND courseid=:courseid");
$stm->execute(array(':userid'=>$userid, ':courseid'=>$sessiondata['paypromptcourse']));
$row = $stm->fetch(PDO::FETCH_NUM);
$stuid = $row[0];
$custominfo = unserialize($row[1]);

if (isset($_GET['cancel'])) {
	$custominfo['payprompttime'] = 2000000000;
	$custominfo['cancelled'] = 1;
	//DB $ci = addslashes(serialize($custominfo));
	$ci = serialize($custominfo);

	//DB $query = "UPDATE imas_students SET custominfo='$ci' WHERE id='$stuid'";
	//DB mysql_query($query) or die("Query failed : " . mysql_error());
	$stm = $DBH->prepare("UPDATE imas_students SET custominfo=:custominfo WHERE id=:id");
	$stm->execute(array(':custominfo'=>$ci, ':id'=>$stuid));

	header('Location: ' . $GLOBALS['basesiteurl'] . '/course/course.php?cid='.$sessiondata['paypromptcourse']);
	exit;
} else if (isset($_GET['done'])) {
	$custominfo['payprompttime'] = 2000000000;
	$custominfo['paid'] = 1;
	//DB $ci = addslashes(serialize($custominfo));
	$ci = serialize($custominfo);

	//DB $query = "UPDATE imas_students SET custominfo='$ci',stutype=1 WHERE id='$stuid'";
	//DB mysql_query($query) or die("Query failed : " . mysql_error());
	$stm = $DBH->prepare("UPDATE imas_students SET custominfo=:custominfo,stutype=1 WHERE id=:id");
	$stm->execute(array(':custominfo'=>$ci, ':id'=>$stuid));

	echo '<html><body><div style="text-align:center"><h1>Thanks so much!</h1><p>Have a great rest of your term!</p><p><a href="../course/course.php?cid='.$sessiondata['paypromptcourse'].'">Back to your course</a></p></div></body></html>';
	exit;
} else if (isset($_GET['later'])) {
	$custominfo['payprompttime'] += 7*24*60*60;
	if (isset($custominfo['paypromptn'])) {
		$custominfo['paypromptn']++;
	} else {
		$custominfo['paypromptn'] = 1;
	}
	//DB $ci = addslashes(serialize($custominfo));
	$ci = serialize($custominfo);
	//DB $query = "UPDATE imas_students SET custominfo='$ci' WHERE id='$stuid'";
	//DB mysql_query($query) or die("Query failed : " . mysql_error());
	$stm = $DBH->prepare("UPDATE imas_students SET custominfo=:custominfo WHERE id=:id");
	$stm->execute(array(':custominfo'=>$ci, ':id'=>$stuid));
} else if (isset($_GET['never'])) {
	$custominfo['payprompttime'] = 2000000000;
	$custominfo['paydenied'] = 1;
	//DB $ci = addslashes(serialize($custominfo));
	$ci = serialize($custominfo);
	//DB $query = "UPDATE imas_students SET custominfo='$ci' WHERE id='$stuid'";
	//DB mysql_query($query) or die("Query failed : " . mysql_error());
	$stm = $DBH->prepare("UPDATE imas_students SET custominfo=:custominfo WHERE id=:id");
	$stm->execute(array(':custominfo'=>$ci, ':id'=>$stuid));
} else if (isset($_GET['click'])) {
	$custominfo['payclickthrough'] = 1;
	$custominfo['paylevel'] = $_GET['v'];
	//DB $ci = addslashes(serialize($custominfo));
	$ci = serialize($custominfo);
	//DB $query = "UPDATE imas_students SET custominfo='$ci' WHERE id='$stuid'";
	//DB mysql_query($query) or die("Query failed : " . mysql_error());
	$stm = $DBH->prepare("UPDATE imas_students SET custominfo=:custominfo WHERE id=:id");
	$stm->execute(array(':custominfo'=>$ci, ':id'=>$stuid));
}
?>
