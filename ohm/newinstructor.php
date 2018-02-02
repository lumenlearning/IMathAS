<?php
	require("../init_without_validate.php");
	echo "<link rel=\"stylesheet\" href=\"$imasroot/ohm/forms.css\" type=\"text/css\" />\n";
	$pagetitle = "New instructor account request";
	$placeinhead = "<link rel=\"stylesheet\" href=\"$imasroot/themes/lumen.css\" type=\"text/css\">\n";
	$placeinhead .= '<style type="text/css">div { margin: 0px; padding: 0px;}</style>';
	$nologo = true;
	require("../header.php");
	$pagetitle = "Instructor Account Request";
	require("infoheader.php");


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
				echo "<p>Username <b>".Sanitize::encodeStringForDisplay($_POST['username'])."</b> is already in use.  Please try another</p>\n";
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
				$query = "INSERT INTO imas_users (SID, password, rights, FirstName, LastName, email, homelayout, created_at) ";
				$query .= "VALUES (:SID, :password, :rights, :FirstName, :LastName, :email, :homelayout, :created_at);";
				$stm = $DBH->prepare($query);
				$stm->execute(array(':SID'=>$_POST['username'], ':password'=>$md5pw, ':rights'=>12, ':FirstName'=>$_POST['firstname'], ':LastName'=>$_POST['lastname'], ':email'=>$_POST['email'], ':homelayout'=>$homelayout, ':created_at'=>time()));
				$newuserid = $DBH->lastInsertId();
				if (isset($CFG['GEN']['enrollonnewinstructor'])) {
					$timeNow = time();
					$valbits = array();
					foreach ($CFG['GEN']['enrollonnewinstructor'] as $ncid) {
					  $ncid = intval($ncid);
						$valbits[] = "($newuserid,$ncid,$timeNow)";
					}
					//DB $query = "INSERT INTO imas_students (userid,courseid,created_at) VALUES ".implode(',',$valbits);
					//DB mysql_query($query) or die("Query failed : " . mysql_error());

					$stm = $DBH->query("INSERT INTO imas_students (userid,courseid,created_at) VALUES ".implode(',',$valbits)); //known INTs - safe
				}
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= "From: $installname <$sendfrom>\r\n";
				$subject = "New Instructor Account Request";
				// trim() removes newlines, which prevents SMTP command injection.
				$message = sprintf("Name: %s %s <br/>\n", Sanitize::encodeStringForDisplay($_POST['firstname']),
					Sanitize::encodeStringForDisplay($_POST['lastname']));
				$message .= sprintf("Email: %s <br/>\n", Sanitize::encodeStringForDisplay($_POST['email']));
				$message .= sprintf("School: %s <br/>\n", Sanitize::encodeStringForDisplay($_POST['school']));
				$message .= sprintf("Phone: %s <br/>\n", Sanitize::encodeStringForDisplay($_POST['phone']));
				$message .= sprintf("Username: %s <br/>\n", Sanitize::encodeStringForDisplay($_POST['username']));
				mail($accountapproval,$subject,$message,$headers);

				$now = time();
				//DB $query = "INSERT INTO imas_log (time, log) VALUES ($now, '$str')";
				//DB mysql_query($query) or die("Query failed : " . mysql_error());
				$_POST['verurl'] = Sanitize::url($_POST['verurl']);
				$urldisplay = Sanitize::encodeStringForDisplay($_POST['verurl']);
				$stm = $DBH->prepare("INSERT INTO imas_log (time, log) VALUES (:time, :log)");
				$stm->execute(array(':time'=>$now, ':log'=>"New Instructor Request: $newuserid:: School: {$_POST['school']} <br/> VerificationURL: <a href='{$_POST['verurl']}' target='_blank'>{$urldisplay}</a> <br/> Phone: {$_POST['phone']} <br/>"));

				$reqdata = array('reqmade'=>$now, 'school'=>$_POST['school'], 'phone'=>$_POST['phone'], 'url'=>$_POST['verurl']);
				$stm = $DBH->prepare("INSERT INTO imas_instr_acct_reqs (userid,status,reqdate,reqdata) VALUES (?,0,?,?)");
				$stm->execute(array($newuserid, $now, json_encode($reqdata)));

				$sanitizedFirstName = Sanitize::encodeStringForDisplay($_POST['firstname']);
				$sanitizedUsername = Sanitize::encodeStringForDisplay($_POST['username']);

				$emailMessage = "
<p>
Dear ${sanitizedFirstName},
</p>

<p>
Your instructor account request for username ${sanitizedUsername} is under review.
</p>

<p>
This request is manually verified, so it may take 1-2 business days to process.
In the meantime, you are welcome to log in and explore these resources designed
to orient you to OHM: 
</p>

<ul>
	<li>
		<b>OHM Orientation Course:</b> Documentation and videos to guide you
		through building courses and using OHM.
	</li>
	<li>
		<b>OHM Community Course:</b> A course in which all faculty users can
		connect! Searchable discussion forums to find answers to common
		questions, learn practical tips and tricks, and connect you with other
		OHM faculty users.
	</li>
</ul>

<p>
Once your account is approved, you will have full trial access to all instructor
account features. Your no-cost trial covers a total of 200 student enrollments.
As you explore OHM during the trial period, we’ll reach out to ask for feedback
and confirm your plans to continue using OHM. Information about our low-cost
pricing is available
<a target=\"_blank\" href=\"http://lumenlearning.com/how/payment-options/\">here</a>,
and we’ll work with you at the appropriate point to transition smoothly to paid
support. 
</p>

<p>
Thank you for your interest in OHM!
</p>

<p>
Lumen OHM administrator
</p>
";

				$browserMessage = "
<p>
Your new instructor account request for username ${sanitizedUsername} is under
review.
</p>

<p>
This request is manually verified, so it may take 1-2 business days to process.
In the meantime, you are welcome to log in and explore these resources designed
to orient you to OHM: 
</p>

<ul>
	<li>
		<b>OHM Orientation Course:</b> Documentation and videos to guide you
		through building courses and using OHM.   
	</li>
	<li>
		<b>OHM Community Course:</b> A course in which all faculty users can
		connect! Searchable discussion forums to find answers to common
		questions, learn practical tips and tricks, and connect you with other
		OHM faculty users.
	</li>
</ul>

<p>
Once your account is approved, you will have full trial access to all
instructor account features. Your no-cost trial covers a total of 200
student enrollments.  As you explore OHM during the trial period, we’ll
reach out to ask for feedback and confirm your plans to continue using OHM.
Information about our low-cost pricing is available
<a target=\"_blank\" href=\"http://lumenlearning.com/how/payment-options/\">here</a>,
and we’ll work with you at the appropriate point to transition smoothly to
paid support. 
</p>

<p>
Thank you for your interest in OHM!
</p>

<p>
Note: Sometimes our account approval notification emails get caught in spam
filters, so be sure to check your spam folder if you don’t see a message in
your inbox. 
</p>
";
				mail(Sanitize::emailAddress($_POST['email']),$subject,$emailMessage,$headers);

				echo $browserMessage;
				require("../footer.php");
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
	echo "<div class=lumensignupforms>";
	echo "<div id='headerforms' class='pagetitle'><h2>New Instructor Account Request</h2></div>\n";
	echo '<dl>';
	echo '<dt><b>Note</b>: Instructor accounts are manually verified, and will be provided for teachers at accredited schools and colleges.</dt>';
	echo '<dd>Lumen OHM does not currently provide instructor accounts to parents, home-schools, or tutors.</dd>';
	echo '<dd>Lumen OHM is only intended for use with children and adults over the age of 13. </dd></dl><br/>';
	echo "<form method=post id=newinstrform class=limitaftervalidate action=\"newinstructor.php\" >\n";
	echo "<input class='lumenform form' type=text name=firstname id=firstname placeholder='First Name' value=\"".Sanitize::encodeStringForDisplay($firstname)."\" size=40 aria-label='First Name' required>";
	echo "<input class='lumenform form' type=text name=lastname id=lastname placeholder='Last Name' value=\"".Sanitize::encodeStringForDisplay($lastname)."\" size=40 aria-label='Last Name' required></span>";
	echo "<input class='lumenform form' type=text name=email id=email placeholder='Email' value=\"".Sanitize::encodeStringForDisplay($email)."\" size=40 aria-label='Email' required>";
	echo "<input class='lumenform form' type=text name=phone placeholder='Phone Number' value=\"".Sanitize::encodeStringForDisplay($phone)."\" size=40 aria-label='Phone Number' required>";
	echo "<input class='lumenform form' type=\"text\" name=\"school\" placeholder='School & District / College' value=\"".Sanitize::encodeStringForDisplay($school)."\" size=40 aria-label='School & District / College' required>";
	echo "<p class=directions >* Where your instructor status can be verified</p> <input  class='lumenform form' type=\"text\" name=\"verurl\" value=\"".Sanitize::encodeStringForDisplay($verurl)."\" placeholder='Web Page (e.g. a school directory)' size=40 aria-label='Web Page (e.g. a school directory)' required>";
	// echo "<p class=directionsstar>Web page where your instructor status can be verified</p>";
	echo "<input class='lumenform form' type=text name=username id=username placeholder='Requested Username (letters,numbers, \"_\")' value=\"".Sanitize::encodeStringForDisplay($username)."\" size=40>";
	echo "<input class='lumenform form' placeholder='Requested Password' type=password name=password id=pw1 size=40 aria-label='Password' required>";
	echo "<input class='lumenform form' placeholder='Retype Password' type=password name=password2 id=pw2 size=40 aria-label='Retype Password' required>";
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
