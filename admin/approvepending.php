<?php

require("../init.php");

if ($myrights<100) {exit;}

if (isset($_GET['skipn'])) {
	$offset =  Sanitize::onlyInt(($_GET['skipn']));
} else {
	$offset = 0;
}

if (isset($_GET['go'])) {
	if (isset($_POST['skip'])) {
		$offset++;
	} else 	if (isset($_POST['deny'])) {
		//DB $query = "UPDATE imas_users SET rights=10 WHERE id='{$_POST['id']}'";
		//DB mysql_query($query) or die("Query failed : " . mysql_error());
		$stm = $DBH->prepare("UPDATE imas_users SET rights=10 WHERE id=:id");
		$stm->execute(array(':id'=>$_POST['id']));
		if (isset($CFG['GEN']['enrollonnewinstructor'])) {
			require("../includes/unenroll.php");
			foreach ($CFG['GEN']['enrollonnewinstructor'] as $rcid) {
				unenrollstu($rcid, array(intval($_POST['id'])));
			}
		}
	} else 	if (isset($_POST['approve'])) {
		if ($_POST['group']>-1) {
			$group = intval($_POST['group']);
		} else if (trim($_POST['newgroup'])!='') {
			//DB $query = "INSERT INTO imas_groups (name) VALUES ('{$_POST['newgroup']}')";
			//DB mysql_query($query) or die("Query failed : " . mysql_error());
			//DB $group = mysql_insert_id();
			$stm = $DBH->prepare("INSERT INTO imas_groups (name) VALUES (:name)");
			$stm->execute(array(':name'=>$_POST['newgroup']));
			$group = $DBH->lastInsertId();
		} else {
			$group = 0;
		}
		//DB $query = "UPDATE imas_users SET rights=40,groupid=$group WHERE id='{$_POST['id']}'";
		//DB mysql_query($query) or die("Query failed : " . mysql_error());
		$stm = $DBH->prepare("UPDATE imas_users SET rights=40,groupid=:groupid WHERE id=:id");
		$stm->execute(array(':groupid'=>$group, ':id'=>$_POST['id']));

		//DB $query = "SELECT FirstName,SID,email FROM imas_users WHERE id='{$_POST['id']}'";
		//DB $result = mysql_query($query) or die("Query failed : " . mysql_error());
		//DB $row = mysql_fetch_row($result);
		$stm = $DBH->prepare("SELECT FirstName,SID,email FROM imas_users WHERE id=:id");
		$stm->execute(array(':id'=>$_POST['id']));
		$row = $stm->fetch(PDO::FETCH_NUM);

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "From: $accountapproval\r\n";

		$message = '<style type="text/css">p {margin:0 0 1em 0} </style><p>Hi '.Sanitize::encodeStringForDisplay($row[0]).'</p>';
		if ($installname == "MyOpenMath") {
			$message .= '<p>Welcome to MyOpenMath.  Your account has been activated, and you\'re all set to log in at <a href="https://www.myopenmath.com">MyOpenMath.com</a> as an instructor using the username <b>'.Sanitize::encodeStringForDisplay($row[1]).'</b> and the password you provided.</p>';
			//$message .= '<p>If you haven\'t already looked at it, you may find the <a href="http://www.myopenmath.com/docs/docs.php">Getting Started Guide</a> helpful</p>';
			$message .= '<p>I\'ve signed you up as a "student" in the Support Course, which has forums in which you can ask questions, report problems, or find out about new system improvements.</p>';
			$message .= '<p>I\'ve also signed you up for the MyOpenMath Training Course.  This course has video tutorials and assignments that will walk you through learning how to use MyOpenMath in your classes.</p>';
			$message .= '<p>David Lippman<br/>admin@myopenmath.com<br/>MyOpenMath administrator</p>';
		} else if ($installname=='WAMAP') {
			$message .= 'Welcome to WAMAP.  Your account has been activated, and you\'re all set to log in as an instructor using the username <b>'.Sanitize::encodeStringForDisplay($row[1]).'</b> and the password you provided.</p>';
			//$message .= '<p>If you haven\'t already looked at it, you may find the <a href="http://www.wamap.org/docs/docs.php">Getting Started Guide</a> helpful</p>';
			$message .= '<p>I\'ve signed you up as a "student" in the Support Course, which has forums in which you can ask questions, report problems, or find out about new system improvements.</p>';
			$message .= '<p>I\'ve also signed you up for the WAMAP Training Course.  This course has video tutorials and assignments that will walk you through learning how to use WAMAP in your classes.</p>';
			$message .= '<p>If you are from outside Washington State, please be aware that WAMAP.org is only intended for regular use by Washington State faculty.  You are welcome to use this site for trial purposes.  If you wish to continue using it, we ask you set up your own installation of the IMathAS software, or use MyOpenMath.com if using content built around an open textbook.</p>';

			$message .= '<p>David Lippman<br/>dlippman@pierce.ctc.edu<br/>Instructor, Math @ Pierce College and WAMAP administrator</p>';
		}
		if($installname == "Lumen OHM"){
					$message .= '<p>Welcome to Lumen OHM.  Your account has been activated, and you\'re all set to log in at <a href="https://ohm.lumenlearning.com/">ohm.lumenlearning.com</a> as an instructor using the username <b>'.Sanitize::encodeStringForDisplay($row[1]).'</b> and the password you provided.</p>';
					$message .= '<p>We\'ve signed you up as a "student" in the Support Course, which has forums in which you can ask questions, report problems, or find out about new system improvements.</p>';
					$message .= '<p>We\'ve also signed you up for the OHM Training Course.  This course has video tutorials and assignments that will walk you through learning how to use OHM in your classes.</p>';
					$message .= '<p>Lumen Support <br/>support@lumenlearning.com<br/>Lumen OHM administrator</p>';
		}
		if (isset($CFG['GEN']['useSESmail'])) {
			SESmail($row[2], $accountapproval, $installname . ' Account Approval', $message);
		} else {
			mail($row[2],$installname . ' Account Approval',$message,$headers);
		}
	}
	header('Location: ' . $GLOBALS['basesiteurl'] . "/admin/approvepending.php?skipn=$offset");
	exit;
}

require("../header.php");
//DB $query = "SELECT id,SID,LastName,FirstName,email FROM imas_users WHERE rights=0 OR rights=12 LIMIT 1 OFFSET $offset";
//DB $result = mysql_query($query) or die("Query failed : " . mysql_error());
//DB if (mysql_num_rows($result)==0) {
$stm = $DBH->query("SELECT id,SID,LastName,FirstName,email FROM imas_users WHERE rights=0 OR rights=12 LIMIT 1 OFFSET $offset"); //sanitized above
if ($stm->rowCount()==0) {
	echo 'No one to approve';
} else {
	//DB $row = mysql_fetch_row($result);
	$row = $stm->fetch(PDO::FETCH_NUM);

	//DB $query = "SELECT log FROM imas_log WHERE log LIKE 'New Instructor Request: {$row[0]}::%'";
	//DB $res = mysql_query($query) or die("Query failed : " . mysql_error());
	//DB if (mysql_num_rows($res)>0) {
		//DB $log = explode('::', mysql_result($res,0,0));
	$stm = $DBH->prepare("SELECT time,log FROM imas_log WHERE log LIKE :log");
	$stm->execute(array(':log'=>"New Instructor Request: {$row[0]}::%"));
	if ($stm->rowCount()>0) {
		$reqdata = $stm->fetch(PDO::FETCH_NUM);
		$reqdate = tzdate("D n/j/y, g:i a", $reqdata[0]);
		$log = explode('::', $reqdata[1]);
		$details = $log[1];
	} else {
		$details = '';
	}

	echo '<h2>Account Approval</h2>';
	echo '<form method="post" action="approvepending.php?go=true&amp;skipn='.$offset.'">';
	echo '<input type="hidden" name="email" value="'.Sanitize::encodeStringForDisplay($row[4]).'"/>';
	echo '<input type="hidden" name="id" value="'.Sanitize::encodeStringForDisplay($row[0]).'"/>';
	echo '<p>Username: '.Sanitize::encodeStringForDisplay($row[1]).'<br/>Name: '.Sanitize::encodeStringForDisplay($row[2]).', '.Sanitize::encodeStringForDisplay($row[3]).' ('.Sanitize::encodeStringForDisplay($row[4]).')</p>';
	echo '<p>Request made: '.$reqdate.'</p>';
	if ($details != '') {
		echo "<p>$details</p>";
		if (preg_match('/School:(.*?)<br/',$details,$matches)) {
			echo '<p><a target="checkver" href="https://www.google.com/search?q='.Sanitize::encodeUrlParam($row[3].' '.$row[2].' '.$matches[1]).'">Search</a></p>';
		}
	}
	echo '<p>Group: <select name="group"><option value="-1">New Group</option>';
	//DB $query = "SELECT id,name FROM imas_groups ORDER BY name";
	//DB $result = mysql_query($query) or die("Query failed : " . mysql_error());
	//DB while ($row = mysql_fetch_row($result)) {
	$stm = $DBH->query("SELECT id,name FROM imas_groups ORDER BY name");
	while ($row = $stm->fetch(PDO::FETCH_NUM)) {
		echo '<option value="'.$row[0].'">'.Sanitize::encodeStringForDisplay($row[1]).'</option>';
	}
	echo '</select> New group: <input type="text" name="newgroup" size="20" /></p>';
	echo '<p><input type="submit" name="approve" value="Approve" /> <input type="submit" name="deny" value="Deny" /> <input type="submit" name="skip" value="Skip" /></p>';
	echo '</form>';
}
require("../footer.php");
?>
