<?php
	require("../init_without_validate.php");
	echo "<link rel=\"stylesheet\" href=\"$imasroot/ohm/forms.css\" type=\"text/css\" />\n";
	$pagetitle = "New instructor account request";
	$placeinhead = "<link rel=\"stylesheet\" href=\"$imasroot/themes/lumen.css\" type=\"text/css\">\n";
	$placeinhead .= '<style type="text/css">div { margin: 0px; padding: 0px;}</style>';

	$nologo = true;
	require("../header.php");
	$pagetitle = "Instructor Account Request";

	if (isset($_POST['firstname'])) {
		if (!isset($_POST['agree'])) {
			echo "<p>You must agree to the Terms and Conditions to set up an account</p>";
		} else if ($loginformat!='' && !preg_match($loginformat,$_POST['username'])) {
			echo "<p>Username is invalid</p>";
		} else if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/',$_POST['email'])) {
			echo '<p>Invalid email address</p>';
		} else if ($_POST['firstname']=='' || $_POST['lastname']=='' || $_POST['email']=='' || $_POST['school']=='' || $_POST['verurl']=='' || $_POST['phone']=='' || $_POST['username']=='' || $_POST['password']=='') {
			echo "<p>Please provide all requested information</p>";

		} else if ($_POST['password']!=$_POST['password2']) {
			echo "<p>Passwords entered do not match.</p>";
		} else {
			//DB $query = "SELECT id FROM imas_users WHERE SID='{$_POST['username']}'";
			//DB $result = mysql_query($query) or die("Query failed : " . mysql_error());
			//DB if (mysql_num_rows($result)>0) {
			$stm = $DBH->prepare("SELECT id FROM imas_users WHERE SID=:SID");
			$stm->execute(array(':SID'=>$_POST['username']));
			if ($stm->rowCount()>0) {
				echo "<p>Username <b>{$_POST['username']}</b> is already in use.  Please try another</p>\n";
			} else {
				if (isset($CFG['GEN']['homelayout'])) {
					$homelayout = $CFG['GEN']['homelayout'];
				} else {
					$homelayout = '|0,1,2||0,1';
				}

				require_once("../includes/password.php");
				$md5pw = password_hash($_POST['password'], PASSWORD_DEFAULT);

				//DB $query = "INSERT INTO imas_users (SID, password, rights, FirstName, LastName, email, homelayout) ";
				//DB $query .= "VALUES ('{$_POST['username']}','$md5pw',0,'{$_POST['firstname']}','{$_POST['lastname']}','{$_POST['email']}','$homelayout');";
				//DB mysql_query($query) or die("Query failed : " . mysql_error());
				//DB $newuserid = mysql_insert_id();
				$query = "INSERT INTO imas_users (SID, password, rights, FirstName, LastName, email, homelayout) ";
				$query .= "VALUES (:SID, :password, :rights, :FirstName, :LastName, :email, :homelayout);";
				$stm = $DBH->prepare($query);
				$stm->execute(array(':SID'=>$_POST['username'], ':password'=>$md5pw, ':rights'=>12, ':FirstName'=>$_POST['firstname'], ':LastName'=>$_POST['lastname'], ':email'=>$_POST['email'], ':homelayout'=>$homelayout));
				$newuserid = $DBH->lastInsertId();
				if (isset($CFG['GEN']['enrollonnewinstructor'])) {
					$valbits = array();
					foreach ($CFG['GEN']['enrollonnewinstructor'] as $ncid) {
					  $ncid = intval($ncid);
						$valbits[] = "($newuserid,$ncid)";
					}
					//DB $query = "INSERT INTO imas_students (userid,courseid) VALUES ".implode(',',$valbits);
					//DB mysql_query($query) or die("Query failed : " . mysql_error());

					$stm = $DBH->query("INSERT INTO imas_students (userid,courseid) VALUES ".implode(',',$valbits)); //known INTs - safe
				}
				$now = time();
				//DB $query = "INSERT INTO imas_log (time, log) VALUES ($now, '$str')";
				//DB mysql_query($query) or die("Query failed : " . mysql_error());
				$_POST['verurl'] = Sanitize::fullUrl($_POST['verurl']);
				$urldisplay = Sanitize::encodeStringForDisplay($_POST['verurl']);
				$stm = $DBH->prepare("INSERT INTO imas_log (time, log) VALUES (:time, :log)");
				$stm->execute(array(':time'=>$now, ':log'=>"New Instructor Request: $newuserid:: School: {$_POST['school']} <br/> VerificationURL: <a href='{$_POST['verurl']}' target='_blank'>{$urldisplay}</a> <br/> Phone: {$_POST['phone']} <br/>"));

				$message = "<p>Your new account request has been sent, for username {$_POST['username']}.</p>  ";
				$message .= "<p>This request is processed by hand, so please be patient.  In the meantime, you are welcome to ";
				$message .= "log in an explore as a student; perhaps play around in one of the self-study courses.</p>";
				$message .= "<p>Sometimes our account approval emails get eaten by spam filters.  You can reduce the likelihood by adding $sendfrom to your contacts list.";
				$message .= "If you don't hear anything in a week, go ahead and try logging in with your selected username and password.</p>";
				mail($_POST['email'],$subject,$message,$headers);

				echo $message;
				require("../footer.php");
				exit;
			}
		}
	}
	if (isset($_POST['firstname'])) {$firstname=$_POST['firstname'];} else {$firstname='';}
	if (isset($_POST['lastname'])) {$lasname=$_POST['lastname'];} else {$lastname='';}
	if (isset($_POST['email'])) {$email=$_POST['email'];} else {$email='';}
	if (isset($_POST['phone'])) {$phone=$_POST['phone'];} else {$phone='';}
	if (isset($_POST['school'])) {$school=$_POST['school'];} else {$school='';}
	if (isset($_POST['verurl'])) {$verurl=$_POST['verurl'];} else {$verurl='';}
	if (isset($_POST['username'])) {$username=$_POST['username'];} else {$username='';}
	echo "<div class=lumensignupforms>";
	echo "<div id='headerforms' class='pagetitle'><h2>New Instructor Account Request</h2></div>\n";
	echo '<dl>';
	echo '<dt><b>Note</b>: Instructor accounts are manually verified, and will be provided for teachers at accredited schools and colleges.</dt>';
	echo '<dd>Lumen OHM does not currently provide instructor accounts to parents, home-schools, or tutors.</dd>';
	echo '<dd>Lumen OHM is only intended for use with children and adults over the age of 13. </dd></dl><br/>';
	echo "<form method=\"post\" action=\"newinstructor.php\" onsubmit=\"return passwordchk();\">\n";
	echo "<input class='lumenform form' type=text name=firstname placeholder='First Name' value=\"$firstname\" size=40 aria-label='First Name' required>";
	echo "<input class='lumenform form' type=text name=lastname  placeholder='Last Name' value=\"$lastname\" size=40 aria-label='Last Name' required></span>";
	echo "<input class='lumenform form' type=text name=email     placeholder='Email' value=\"$email\" size=40 aria-label='Email' required>";
	echo "<input class='lumenform form' type=text name=phone placeholder='Phone Number' value=\"$phone\" size=40 aria-label='Phone Number' required>";
	echo "<input class='lumenform form' type=\"text\" name=\"school\" placeholder='School & District / College' value=\"$school\" size=40 aria-label='School & District / College' required>";
	echo "<p class=directions >* Where your instructor status can be verified</p> <input  class='lumenform form' type=\"text\" name=\"verurl\" value=\"$verurl\" placeholder='Web Page (e.g. a school directory)' size=40 aria-label='Web Page (e.g. a school directory)' required>";
	// echo "<p class=directionsstar>Web page where your instructor status can be verified</p>";
	echo "<input  class='lumenform form' type=text name=username placeholder='Requested Username (letters,numbers, \"_\")' value=\"$username\" size=40>";
	echo "<input  class='lumenform form' placeholder='Requested Password' type=password name=password id=\"password\" size=40 aria-label='Password' required>";
	echo "<input  class='lumenform form' placeholder='Retype Password' type=password name=password2 id=\"password2\" size=40 aria-label='Retype Password' required>";
	//echo "<span class=form>I have read and agree to the Terms of Use (below)</span><span class=formright><input type=checkbox name=agree></span><br class=form />\n";
	if (isset($CFG['GEN']['TOSpage'])) {
		echo "</br>
		<label class=form>
			<input type=checkbox name=agree id=agree aria-label=agree  aria-label='agree' required/>
			<span>I have read and agree to the <a href=\"#\" onclick=\"GB_show('Terms of Use','".$CFG['GEN']['TOSpage']."',700,500);return false;\">Terms of Use</a></span>
		</label></br>";
	}
	echo "<button class=button type=submit>Submit</button>";
	echo "</form>\n";
	echo "</div>";

	require("../footer.php");
?>
