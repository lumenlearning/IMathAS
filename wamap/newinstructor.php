<?php
	
	require("../config.php");
	$pagetitle = "New instructor account request";
	$placeinhead = "<link rel=\"stylesheet\" href=\"$imasroot/wamap/infopages.css\" type=\"text/css\">\n";
	$nologo = true;
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
			$query = "SELECT id FROM imas_users WHERE SID='{$_POST['username']}'";
			$result = mysql_query($query) or die("Query failed : " . mysql_error());
			if (mysql_num_rows($result)>0) {
				echo "<p style=\"color:red\">Username <b>{$_POST['username']}</b> is already in use.  Please try another</p>\n";
			} else {
				$query = "INSERT INTO imas_users (SID, password, rights, FirstName, LastName, email) ";
				$md5pw = md5($_POST['password']);
				$query .= "VALUES ('{$_POST['username']}','$md5pw',0,'{$_POST['firstname']}','{$_POST['lastname']}','{$_POST['email']}');";
				mysql_query($query) or die("Query failed : " . mysql_error());
				$newuserid = mysql_insert_id();
				$query = "INSERT INTO imas_students (userid,courseid) VALUES ('$newuserid',1),('$newuserid',438)";
				mysql_query($query) or die("Query failed : " . mysql_error());
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= "From: $installname <$sendfrom>\r\n";
				$subject = "New Instructor Account Request";
				$message = "Name: {$_POST['firstname']} {$_POST['lastname']} <br/>\n";
				$message .= "Email: {$_POST['email']} <br/>\n";
				$message .= "School: {$_POST['school']} <br/>\n";
				$message .= "VerificationURL: {$_POST['verurl']} <br/>\n";
				$message .= "Phone: {$_POST['phone']} <br/>\n";
				$message .= "Username: {$_POST['username']} <br/>\n";
				mail($newacctemail,$subject,$message,$headers);
				
				$now = time();
				$query = "INSERT INTO imas_log (time, log) VALUES ($now, 'New Instructor Request: $newuserid:: School: {$_POST['school']} <br/> VerificationURL: {$_POST['verurl']} <br/> Phone: {$_POST['phone']} <br/>')";
				mysql_query($query) or die("Query failed : " . mysql_error());
				
				
				$message = "<p>Your new account request has been sent.</p>  ";
				$message .= "<p>This request is processed by hand, so please be patient.</p>";
				$message .= "<p>While it is being processed, you may ";
				$message .= "wish to read some of the new user documentation available at <a href=\"http://www.wamap.org/docs/docs.html\">http://www.wamap.org/docs/docs.html</a></p>\n";
				mail($_POST['email'],$subject,$message,$headers);
				
				echo $message;
				require("footer.php");
				exit;
			}
		}
	}
	if (isset($_POST['firstname'])) {$firstname=$_POST['firstname'];} else {$firstname='';}
	if (isset($_POST['lastname'])) {$lastname=$_POST['lastname'];} else {$lastname='';}
	if (isset($_POST['email'])) {$email=$_POST['email'];} else {$email='';}
	if (isset($_POST['phone'])) {$phone=$_POST['phone'];} else {$phone='';}
	if (isset($_POST['school'])) {$school=$_POST['school'];} else {$school='';}
	if (isset($_POST['verurl'])) {$verurl=$_POST['verurl'];} else {$verurl='';}
	if (isset($_POST['username'])) {$username=$_POST['username'];} else {$username='';}
	
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
	echo "<span class=form>I have read and agree to the Terms of Use (below)</span><span class=formright><input type=checkbox name=agree></span><br class=form />\n";
	echo "<div class=submit><input type=submit value=\"Request Account\"></div>\n";
	echo "</form>\n";
	echo "<h4>Terms of Use</h4>\n";
	echo "<p><em>There is <strong>no warranty</strong> and <strong>no guarantees</strong> attached with this offer.  The ";
	echo "server or software might crash or mysteriously lose all your data.  Your account or this service may be terminated without warning.  ";
	echo "Instructor technical support is provided by volunteers and the user community, and is thus limited and not guaranteed.  No student technical support is provided.  ";
	echo "Use of this system is at your own risk.</em></p>\n";
	echo "<p><em>Copyrighted materials should not be posted or used in questions without the permission of the copyright owner.  You shall be solely ";
	echo "responsible for your own user created content and the consequences of posting or publishing them.  WAMAP expressly disclaims any and all liability in ";
	echo "connection with user created content.</em></p>";
	echo "<p><em>If you create questions, assignments, or courses and set them to be available for use by other instructors, through that action you are ";
	echo "granting explicit rights for other instructors to use, copy, and redistribute modifications of your items to other instructors without compensation or attribution. ";
	echo "This includes redistribution to other non-commercial installations of the IMathAS software.</em></p>";
	require("../footer.php");
?>
