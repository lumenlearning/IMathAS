<?php

	require("../init_without_validate.php");
	require_once(__DIR__.'/../includes/newusercommon.php');
	$pagetitle = "New instructor account request";
	$placeinhead = "<link rel=\"stylesheet\" href=\"$imasroot/wamap/infopages.css\" type=\"text/css\">\n";
	$placeinhead .= '<script type="text/javascript" src="'.$imasroot.'/javascript/jquery.validate.min.js"></script>';
	$placeinhead .= '<style type="text/css">div { margin: 0px; padding: 0px;}</style>';
	$nologo = true;
	$flexwidth = true;
	require("../header.php");
	$pagetitle = "Instructor Account Request";
	require("infoheader.php");
	$extrarequired = array('school','phone','verurl','agree');
	
	if (isset($_POST['firstname'])) {
		$error = '';
		if (!isset($_POST['agree'])) {
			$error .= "<p>You must agree to the Terms and Conditions to set up an account</p>";
		}

		$error .= checkNewUserValidation($extrarequired);

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
			$query = "INSERT INTO imas_users (SID, password, rights, FirstName, LastName, email, homelayout) ";
			$query .= "VALUES (:SID, :password, :rights, :FirstName, :LastName, :email, :homelayout);";
			$stm = $DBH->prepare($query);
			$stm->execute(array(':SID'=>$_POST['SID'], ':password'=>$md5pw, ':rights'=>12, ':FirstName'=>$_POST['firstname'], ':LastName'=>$_POST['lastname'], ':email'=>$_POST['email'], ':homelayout'=>$homelayout));
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
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= "From: $installname <$sendfrom>\r\n";
			$subject = "New Instructor Account Request";
			$message = sprintf("Name: %s %s <br/>\n", Sanitize::encodeStringForDisplay($_POST['firstname']),
				Sanitize::encodeStringForDisplay($_POST['lastname']));
			$message .= sprintf("Email: %s <br/>\n", Sanitize::encodeStringForDisplay($_POST['email']));
			$message .= sprintf("School: %s <br/>\n", Sanitize::encodeStringForDisplay($_POST['school']));
			$message .= sprintf("Phone: %s <br/>\n", Sanitize::encodeStringForDisplay($_POST['phone']));
			$message .= sprintf("Username: %s <br/>\n", Sanitize::encodeStringForDisplay($_POST['SID']));
			mail($accountapproval,$subject,$message,$headers);

			$now = time();
			//DB $query = "INSERT INTO imas_log (time, log) VALUES ($now, 'New Instructor Request: $newuserid:: School: {$_POST['school']} <br/> VerificationURL: {$_POST['verurl']} <br/> Phone: {$_POST['phone']} <br/>')";
			//DB mysql_query($query) or die("Query failed : " . mysql_error());
			$stm = $DBH->prepare("INSERT INTO imas_log (time, log) VALUES (:time, :log)");
			if (substr($_POST['verurl'],0,4)=='http') {
				$_POST['verurl'] = Sanitize::url($_POST['verurl']);
				$urldisplay = Sanitize::encodeStringForDisplay($_POST['verurl']);
				$urlstring = "<a href='{$_POST['verurl']}' target='_blank'>{$urldisplay}</a>";
			} else {
				$urlstring = Sanitize::encodeStringForDisplay($_POST['verurl']);
			}
			
			$stm->execute(array(':time'=>$now, ':log'=>"New Instructor Request: $newuserid:: School: {$_POST['school']} <br/> VerificationURL: $urlstring <br/> Phone: {$_POST['phone']} <br/>"));

			$reqdata = array('reqmade'=>$now, 'school'=>$_POST['school'], 'phone'=>$_POST['phone'], 'url'=>$_POST['verurl']);
			$stm = $DBH->prepare("INSERT INTO imas_instr_acct_reqs (userid,status,reqdate,reqdata) VALUES (?,0,?,?)");
			$stm->execute(array($newuserid, $now, json_encode($reqdata)));

			$message = "<p>Your new account request has been sent.</p>  ";
			$message .= "<p>This request is processed by hand, so please be patient.</p>";
			$message .= "<p>Sometimes our account approval emails get eaten by spam filters.  You can reduce the likelihood by adding $sendfrom and $accountapproval to your contacts list.";
			$message .= "If you don't hear anything in a week, go ahead and try logging in with your selected username and password.</p>";
			mail(Sanitize::emailAddress($_POST['email']),$subject,$message,$headers);

			echo $message;
			require("../footer.php");
			exit;
			
		}
	}
	if (isset($_POST['firstname'])) {$firstname=$_POST['firstname'];} else {$firstname='';}
	if (isset($_POST['lastname'])) {$lasname=$_POST['lastname'];} else {$lastname='';}
	if (isset($_POST['email'])) {$email=$_POST['email'];} else {$email='';}
	if (isset($_POST['phone'])) {$phone=$_POST['phone'];} else {$phone='';}
	if (isset($_POST['school'])) {$school=$_POST['school'];} else {$school='';}
	if (isset($_POST['verurl'])) {$verurl=$_POST['verurl'];} else {$verurl='';}
	if (isset($_POST['SID'])) {$username=$_POST['SID'];} else {$username='';}

	echo "<h3>New Instructor Account Request</h3>\n";
	echo "<p>The IMathAS software and this webserver hosting are offered free of charge for use by instructors and their students from ";
	echo "<b>Washington State</b> schools and colleges.  Users from other locations are encouraged to use our sister site <a href=\"http://www.myopenmath.com\">MyOpenMath</a>,";
	echo "and <b>will not</b> be given accounts on WAMAP unless you specifically <a href=\"mailto:dlippman@pierce.ctc.edu\">email me</a> to tell me why you ";
	echo "want a WAMAP account.  WAMAP is only intended for use with children and adults over the age of 13.</p>";
	echo "<form method=post id=newinstrform class=limitaftervalidate action=\"newinstructor.php\" >\n";
	echo "<span class=form>First Name</span><span class=formright><input type=text name=firstname id=firstname value=\"".Sanitize::encodeStringForDisplay($firstname)."\" size=40></span><br class=form />\n";
	echo "<span class=form>Last Name</span><span class=formright><input type=text name=lastname id=lastname value=\"".Sanitize::encodeStringForDisplay($lastname)."\" size=40></span><br class=form />\n";
	echo "<span class=form>Email Address</span><span class=formright><input type=text name=email id=email value=\"".Sanitize::encodeStringForDisplay($email)."\" size=40></span><br class=form />\n";
	echo '<span class=form></span><span class="formright small">For fastest approval, use your .edu address. You can change it later.</span><br class=form />';
	echo "<span class=form>Phone Number</span><span class=formright><input type=text name=phone id=phone value=\"".Sanitize::encodeStringForDisplay($phone)."\" size=40></span><br class=form />\n";
	echo "<span class=form>School &amp; District / College</span><span class=formright><input type=text name=school id=school value=\"".Sanitize::encodeStringForDisplay($school)."\" size=40></span><br class=form />\n";
	echo '<span class=form></span><span class="formright small">Give the full name. There are a lot of BCC\'s out there, so abbreviations slow us down.</span><br class=form />';
	echo "<span class=form>Instructor verification page</span><span class=formright><input type=text name=verurl id=verurl value=\"".Sanitize::encodeStringForDisplay($verurl)."\" size=40></span><br class=form />\n";
	echo '<span class=form></span><span class="formright small">Provide a direct link to a web page showing you, or to the school\'s online directory. Make sure the site doesn\'t require a login. ';
	echo 'If you just type something like "see school directory" or provide a link to the college\'s home page, ';
	echo 'our volunteers will probably decide your request is too much work and delay approving it. Don\'t say "call ____"; we won\'t. ';
	echo 'If you are not listed on a website, you can have your supervisor (someone who can be verified) email accounts@wamap.org, and enter here "see email from ___".</span><br class=form />';
	echo "<span class=form>Requested Username (use only letters, numbers, and the _ character)</span><span class=formright><input type=text name=SID id=SID value=\"".Sanitize::encodeStringForDisplay($username)."\" size=40></span><br class=form />\n";
	echo "<span class=form>Requested Password</span><span class=formright><input type=password name=pw1 id=pw1 size=40></span><br class=form />\n";
	echo "<span class=form>Retype Password</span><span class=formright><input type=password name=pw2 id=pw2 size=40></span><br class=form />\n";
	//echo "<span class=form>I have read and agree to the Terms of Use (below)</span><span class=formright><input type=checkbox name=agree></span><br class=form />\n";
	if (isset($CFG['GEN']['TOSpage'])) {
		echo "<span class=form><label for=\"agree\">I have read and agree to the <a href=\"#\" onclick=\"GB_show('Terms of Use','".$CFG['GEN']['TOSpage']."',700,500);return false;\">Terms of Use</a></label></span><span class=formright><input type=checkbox name=agree></span><br class=form />\n";
	}
	echo "<div class=submit><input type=submit value=\"Request Account\"></div>\n";
	echo "</form>\n";
	showNewUserValidation('newinstrform',$extrarequired);
	require("../footer.php");
?>
