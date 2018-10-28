<?php
	require("../init_without_validate.php");
	require_once('../includes/newusercommon.php');
	echo "<link rel=\"stylesheet\" href=\"$imasroot/ohm/forms.css\" type=\"text/css\" />\n";
	$pagetitle = "New instructor account request";
	$placeinhead = "<link rel=\"stylesheet\" href=\"$imasroot/themes/lumen.css\" type=\"text/css\">\n";
	$placeinhead .= '<script type="text/javascript" src="'.$imasroot.'/javascript/jquery.validate.min.js"></script>';
	$placeinhead .= '<style type="text/css">div { margin: 0px; padding: 0px;}</style>';
	$nologo = true;
	require("../header.php");
	$pagetitle = "Instructor Account Request";
	require("infoheader.php");
	$extrarequired = array('school','phone','verurl','agree');
	
	if (isset($_POST['firstname'])) {
		$error = '';
		if (!isset($_POST['agree'])) {
			$error .= "<p>You must agree to the Terms and Conditions to set up an account</p>";
		}
		$error .= checkNewUserValidation(array_merge($extrarequired, array('SID','firstname','lastname','email','pw1','pw2')));

		if ($error != '') {
			echo $error;
		} else {
				if (isset($CFG['GEN']['homelayout'])) {
					$homelayout = $CFG['GEN']['homelayout'];
				} else {
					$homelayout = '|0,1,2||0,1';
				}

				require_once("../includes/password.php");
				$md5pw = password_hash($_POST['pw1'], PASSWORD_DEFAULT);

				//DB $query = "INSERT INTO imas_users (SID, password, rights, FirstName, LastName, email, homelayout) ";
				//DB $query .= "VALUES ('{$_POST['username']}','$md5pw',0,'{$_POST['firstname']}','{$_POST['lastname']}','{$_POST['email']}','$homelayout');";
				//DB mysql_query($query) or die("Query failed : " . mysql_error());
				//DB $newuserid = mysql_insert_id();
				$query = "INSERT INTO imas_users (SID, password, rights, FirstName, LastName, email, homelayout, created_at) ";
				$query .= "VALUES (:SID, :password, :rights, :FirstName, :LastName, :email, :homelayout, :created_at);";
				$stm = $DBH->prepare($query);
				$stm->execute(array(':SID'=>$_POST['SID'], ':password'=>$md5pw, ':rights'=>12, ':FirstName'=>$_POST['firstname'], ':LastName'=>$_POST['lastname'], ':email'=>$_POST['email'], ':homelayout'=>$homelayout, ':created_at'=>time()));
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


				$now = time();
				//DB $query = "INSERT INTO imas_log (time, log) VALUES ($now, '$str')";
				//DB mysql_query($query) or die("Query failed : " . mysql_error());
				$_POST['verurl'] = Sanitize::fullUrl($_POST['verurl']);
				$urldisplay = Sanitize::encodeStringForDisplay($_POST['verurl']);
				$stm = $DBH->prepare("INSERT INTO imas_log (time, log) VALUES (:time, :log)");
				$stm->execute(array(':time'=>$now, ':log'=>"New Instructor Request: $newuserid:: School: {$_POST['school']} <br/> VerificationURL: <a href='{$_POST['verurl']}' target='_blank'>{$urldisplay}</a> <br/> Phone: {$_POST['phone']} <br/>"));

				$reqdata = array('reqmade'=>$now, 'school'=>$_POST['school'], 'phone'=>$_POST['phone'], 'url'=>$_POST['verurl']);
				$stm = $DBH->prepare("INSERT INTO imas_instr_acct_reqs (userid,status,reqdate,reqdata) VALUES (?,0,?,?)");
				$stm->execute(array($newuserid, $now, json_encode($reqdata)));

				$sanitizedFirstName = Sanitize::encodeStringForDisplay($_POST['firstname']);
				$sanitizedUsername = Sanitize::encodeStringForDisplay($_POST['SID']);

				$emailMessage = "
<p>
	Hi ${sanitizedFirstName},
</p>

<p>
	Thank you for your OHM instructor account request! In order to grant
	instructor access, we need to verify your educator status and affiliation.
	Once verification is complete, your account will be elevated to a Lumen’s
	OHM instructor account. Account verification is typically completed within
	2 business days.
</p>

<p>
	Once your instructor account is approved, periodically we’ll send you tips
	to assist you in getting the most out of OHM. In the meantime, you can
	watch our
	<a target='_blank' href='https://www.youtube.com/watch?v=ApDlMfNU8HM&feature=youtu.be'>overview video</a>
	to familiarize yourself with the OHM platform.
</p>

<p>
	We appreciate your interest in using Open Education Resources (OER) to
	increase student access and affordability of high-quality math courseware!
</p>

<p>
	The Lumen Team
</p>
";

				$browserMessage = "
<p>
Your new instructor account request for username ${sanitizedUsername} is under
review and will take 1-2 business days to process.
</p>

<p>
In the meantime, watch this
<a target='_blank' href='https://www.youtube.com/watch?v=ApDlMfNU8HM&feature=youtu.be'>short video</a>
to learn how to use OHM to increase student engagement and learning.
</p>

<p>
<strong>Note:</strong>
<em>If you haven’t received your account verification status email, check your
spam filter.</em>
</p>
";
				if (isset($CFG['GEN']['useSESmail'])) {
					SESmail(Sanitize::emailAddress($_POST['email']), $accountapproval, $subject, $emailMessage);
				} else {
					mail(Sanitize::emailAddress($_POST['email']),$subject,$emailMessage,$headers);
				}

				echo $browserMessage;
				require("../footer.php");
				exit;
		}
	}
	if (isset($_POST['firstname'])) {$firstname=$_POST['firstname'];} else {$firstname='';}
	if (isset($_POST['lastname'])) {$lastname=$_POST['lastname'];} else {$lastname='';}
	if (isset($_POST['email'])) {$email=$_POST['email'];} else {$email='';}
	if (isset($_POST['phone'])) {$phone=$_POST['phone'];} else {$phone='';}
	if (isset($_POST['school'])) {$school=$_POST['school'];} else {$school='';}
	if (isset($_POST['verurl'])) {$verurl=$_POST['verurl'];} else {$verurl='';}
	if (isset($_POST['SID'])) {$username=$_POST['SID'];} else {$username='';}
	echo "<div class=lumensignupforms>";
	echo "<div id='headerforms' class='pagetitle'><h2>New Instructor Account Request</h2></div>\n";
	echo '<dl>';
	echo '<dt><b>Note</b>: Instructor accounts are manually verified, and will be provided for teachers at accredited schools and colleges.</dt>';
	echo '<dd>Lumen OHM does not currently provide instructor accounts to parents, home-schools, or tutors.</dd>';
	echo '<dd>Lumen OHM is only intended for use with children and adults over the age of 13. </dd></dl><br/>';
	echo "<form method=post id=newinstrform class=limitaftervalidate action=\"newinstructor.php\" >\n";
	echo "<input class='lumenform form' type=text name=firstname id=firstname placeholder='First Name' value=\"".Sanitize::encodeStringForDisplay($firstname)."\" size=40 aria-label='First Name' required>";
	echo "<input class='lumenform form' type=text name=lastname id=lastname placeholder='Last Name' value=\"".Sanitize::encodeStringForDisplay($lastname)."\" size=40 aria-label='Last Name' required></span>";
	echo "<input class='lumenform form' type=text name=email id=email placeholder='School Email' value=\"".Sanitize::encodeStringForDisplay($email)."\" size=40 aria-label='Email' required>";
	echo "<input class='lumenform form' type=text name=phone placeholder='Phone Number' value=\"".Sanitize::encodeStringForDisplay($phone)."\" size=40 aria-label='Phone Number' required>";
	echo "<input class='lumenform form' type=\"text\" name=\"school\" placeholder='School & District / College' value=\"".Sanitize::encodeStringForDisplay($school)."\" size=40 aria-label='School & District / College' required>";
	echo "<p class=directions >* Where your instructor status can be verified</p> <input  class='lumenform form' type=\"text\" name=\"verurl\" value=\"".Sanitize::encodeStringForDisplay($verurl)."\" placeholder='Web Page (e.g. a school directory)' size=40 aria-label='Web Page (e.g. a school directory)' required>";
	// echo "<p class=directionsstar>Web page where your instructor status can be verified</p>";
	echo "<input class='lumenform form' type=text name=SID id=SID placeholder='Requested Username (letters,numbers, \"_\")' value=\"".Sanitize::encodeStringForDisplay($username)."\" size=40>";
	echo "<input class='lumenform form' placeholder='Requested Password' type=password name=pw1 id=pw1 size=40 aria-label='Password' required>";
	echo "<input class='lumenform form' placeholder='Retype Password' type=password name=pw2 id=pw2 size=40 aria-label='Retype Password' required>";
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
	showNewUserValidation('newinstrform',$extrarequired);
	require("../footer.php");
?>
