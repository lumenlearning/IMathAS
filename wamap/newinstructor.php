<?php

	require("../init_without_validate.php");
	$pagetitle = "New instructor account request";
	$placeinhead = "<link rel=\"stylesheet\" href=\"$imasroot/wamap/infopages.css\" type=\"text/css\">\n";
	$nologo = true;
	$flexwidth = true;
	require("../header.php");
	$pagetitle = "Instructor Account Request";
	require("infoheader.php");

	if (isset($_POST['firstname'])) {
		if ($_POST['firstname']=='' || $_POST['lastname']=='' || $_POST['email']=='' || $_POST['school']=='' || $_POST['verurl']=='' || $_POST['phone']=='' || $_POST['username']=='' || $_POST['password']=='') {
			echo "<p style=\"color:red\">Please provide all requested information</p>";
		} else if (!isset($_POST['agree'])) {
			echo "<p style=\"color:red\">You must agree to the Terms and Conditions to set up an account</p>";
		} else if (!preg_match('/^[\w+\.]+$/',$_POST['username'])) {
			echo "<p style=\"color:red\">requested username is invalid. </p>";
		} else if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/',$_POST['email'])) {
			echo "<p style=\"color:red\">Invalid email address. </p>";
		} else if ($_POST['password']!=$_POST['password2']) {
			echo "<p style=\"color:red\">Passwords entered do not match.</p>";
		} else {
			//DB $query = "SELECT id FROM imas_users WHERE SID='{$_POST['username']}'";
			//DB $result = mysql_query($query) or die("Query failed : " . mysql_error());
			//DB if (mysql_num_rows($result)>0) {
			$stm = $DBH->prepare("SELECT id FROM imas_users WHERE SID=:SID");
			$stm->execute(array(':SID'=>$_POST['username']));
			if ($stm->rowCount()>0) {
				echo "<p style=\"color:red\">Username <b>".Sanitize::encodeStringForDisplay($_POST['username'])."</b> is already in use.  Please try another</p>\n";
			} else {
				if (isset($CFG['GEN']['homelayout'])) {
					$homelayout = $CFG['GEN']['homelayout'];
				} else {
					$homelayout = '|0,1,2||0,1';
				}
				require_once("../includes/password.php");
				$md5pw = password_hash($_POST['password'], PASSWORD_DEFAULT);
				//DB $query = "INSERT INTO imas_users (SID, password, rights, FirstName, LastName, email, homelayout) ";
				//DB $query .= "VALUES ('{$_POST['username']}','$md5pw',12,'{$_POST['firstname']}','{$_POST['lastname']}','{$_POST['email']}', '$homelayout');";
				//DB mysql_query($query) or die("Query failed : " . mysql_error());
				//DB $newuserid = mysql_insert_id();
				$query = "INSERT INTO imas_users (SID, password, rights, FirstName, LastName, email, homelayout) ";
				$query .= "VALUES (:SID, :password, :rights, :FirstName, :LastName, :email, :homelayout);";
				$stm = $DBH->prepare($query);
				$stm->execute(array(':SID'=>$_POST['username'], ':password'=>$md5pw, ':rights'=>12, ':FirstName'=>$_POST['firstname'], ':LastName'=>$_POST['lastname'], ':email'=>$_POST['email'], ':homelayout'=>$homelayout));
				$newuserid = $DBH->lastInsertId();
				//DB $query = "INSERT INTO imas_students (userid,courseid) VALUES ('$newuserid',1),('$newuserid',438)";
				//DB mysql_query($query) or die("Query failed : " . mysql_error());
				$stm = $DBH->prepare("INSERT INTO imas_students (userid,courseid) VALUES (:userid, :courseid),(:userid2, :courseid2)");
				$stm->execute(array(':userid'=>$newuserid, ':courseid'=>1, ':userid2'=>$newuserid, ':courseid2'=>438));

				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= "From: $installname <$sendfrom>\r\n";
				$subject = "New Instructor Account Request";
				$message = sprintf("Name: %s %s <br/>\n", Sanitize::encodeStringForDisplay($_POST['firstname']),
					Sanitize::encodeStringForDisplay($_POST['lastname']));
				$message .= sprintf("Email: %s <br/>\n", Sanitize::encodeStringForDisplay($_POST['email']));
				$message .= sprintf("School: %s <br/>\n", Sanitize::encodeStringForDisplay($_POST['school']));
				$message .= sprintf("VerificationURL: %s <br/>\n", Sanitize::url($_POST['verurl']));
				$message .= sprintf("Phone: %s <br/>\n", Sanitize::encodeStringForDisplay($_POST['phone']));
				$message .= sprintf("Username: %s <br/>\n", Sanitize::encodeStringForDisplay($_POST['username']));
				mail($newacctemail,$subject,$message,$headers);

				$now = time();
				//DB $query = "INSERT INTO imas_log (time, log) VALUES ($now, 'New Instructor Request: $newuserid:: School: {$_POST['school']} <br/> VerificationURL: {$_POST['verurl']} <br/> Phone: {$_POST['phone']} <br/>')";
				//DB mysql_query($query) or die("Query failed : " . mysql_error());
				$_POST['verurl'] = Sanitize::url($_POST['verurl']);
				$urldisplay = Sanitize::encodeStringForDisplay($_POST['verurl']);
				$stm = $DBH->prepare("INSERT INTO imas_log (time, log) VALUES (:time, :log)");
				$stm->execute(array(':time'=>$now, ':log'=>"New Instructor Request: $newuserid:: School: {$_POST['school']} <br/> VerificationURL: <a href='{$_POST['verurl']}' target='_blank'>{$urldisplay}</a> <br/> Phone: {$_POST['phone']} <br/>"));


				$message = "<p>Your new account request has been sent.</p>  ";
				$message .= "<p>This request is processed by hand, so please be patient.</p>";
				$message .= "<p>Sometimes our account approval emails get eaten by spam filters.  You can reduce the likelihood by adding ".Sanitize::emailAddress($sendfrom)." to your contacts list.";
				$message .= "If you don't hear anything in a week, go ahead and try logging in with your selected username and password.</p>";
				mail(Sanitize::emailAddress($_POST['email']),$subject,$message,$headers);

				echo $message;
				require("../footer.php");
				exit;
			}
		}
	}
	if (isset($_POST['firstname'])) {$firstname=Sanitize::encodeStringForDisplay($_POST['firstname']);} else {$firstname='';}
	if (isset($_POST['lastname'])) {$lastname=Sanitize::encodeStringForDisplay($_POST['lastname']);} else {$lastname='';}
	if (isset($_POST['email'])) {$email=Sanitize::encodeStringForDisplay($_POST['email']);} else {$email='';}
	if (isset($_POST['phone'])) {$phone=Sanitize::encodeStringForDisplay($_POST['phone']);} else {$phone='';}
	if (isset($_POST['school'])) {$school=Sanitize::encodeStringForDisplay($_POST['school']);} else {$school='';}
	if (isset($_POST['verurl'])) {$verurl=Sanitize::url($_POST['verurl']);} else {$verurl='';}
	if (isset($_POST['username'])) {$username=Sanitize::encodeStringForDisplay($_POST['username']);} else {$username='';}

	echo "<h3>New Instructor Account Request</h3>\n";
	echo "<p>The IMathAS software and this webserver hosting are offered free of charge for use by instructors and their students from ";
	echo "<b>Washington State</b> schools and colleges.  Users from other locations are encouraged to use our sister site <a href=\"http://www.myopenmath.com\">MyOpenMath</a>,";
	echo "and <b>will not</b> be given accounts on WAMAP unless you specifically <a href=\"mailto:dlippman@pierce.ctc.edu\">email me</a> to tell me why you ";
	echo "want a WAMAP account.</p>";
	echo "<form method=post action=\"newinstructor.php\" onsubmit=\"return passwordchk();\">\n";
	echo "<span class=form>First Name</span><span class=formright><input type=text name=firstname value=\"$firstname\" size=40></span><br class=form />\n";
	echo "<span class=form>Last Name</span><span class=formright><input type=text name=lastname value=\"$lastname\" size=40></span><br class=form />\n";
	echo "<span class=form>Email Address<br/><span style=\"font-size: 75%\">Use an official .edu email if you have one, please.</span></span><span class=formright><input type=text name=email value=\"$email\" size=40></span><br class=form />\n";
	echo "<span class=form>Phone Number</span><span class=formright><input type=text name=phone value=\"$phone\" size=40></span><br class=form />\n";
	echo "<span class=form>School &amp; District / College</span><span class=formright><input type=text name=school value=\"$school\" size=40></span><br class=form />\n";
	echo "<span class=form>Web address of a page on your school web site listing you as an instructor<br/><span style=\"font-size: 75%\">Or a link to your school's staff directory.  If we can't verify you are an instructor easily, it will delay your account approval, or your request will be ignored.</span></span><span class=formright><input type=text name=verurl value=\"$verurl\" size=40></span><br class=form />\n";

	echo "<span class=form>Requested Username<br/><span style=\"font-size: 75%\">Use only numbers, letters, . or the _ character.</span></span><span class=formright><input type=text name=username value=\"$username\" size=40></span><br class=form />\n";
	echo "<span class=form>Requested Password</span><span class=formright><input type=password name=password id=\"password\" size=40></span><br class=form />\n";
	echo "<span class=form>Retype Password</span><span class=formright><input type=password name=password2 id=\"password2\" size=40></span><br class=form />\n";
	//echo "<span class=form>I have read and agree to the Terms of Use (below)</span><span class=formright><input type=checkbox name=agree></span><br class=form />\n";
	if (isset($CFG['GEN']['TOSpage'])) {
		echo "<span class=form><label for=\"agree\">I have read and agree to the <a href=\"#\" onclick=\"GB_show('Terms of Use','".$CFG['GEN']['TOSpage']."',700,500);return false;\">Terms of Use</a></label></span><span class=formright><input type=checkbox name=agree></span><br class=form />\n";
	}
	echo "<div class=submit><input type=submit value=\"Request Account\"></div>\n";
	echo "</form>\n";

	require("../footer.php");
?>
