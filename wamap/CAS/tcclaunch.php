<?php
//IMathAS:  CAS SSO Launch
//(c) David Lippman 2010

//adjusted paths 10/5/13 for directory change.  Did not change urls b/c of mod_rewrite

error_reporting(0);

include("../../init_without_validate.php");
require_once("../../includes/password.php");
$ltiorg = 'tacomacc.edu';
$ltiorgname = 'Tacoma CC';

$infoerr = '';

function reporterror($err) {
	require("../../header.php");
	echo "<p>$err</p>";
	require("../../footer.php");
	exit;
}
if (isset($_GET['cid'])) {
	$cid = intval($_GET['cid']);
	$cidqs = '&cid='.$cid;
} else {
	$cidqs = '';
}

//start session
if (isset($sessionpath)) { session_save_path($sessionpath);}
ini_set('session.gc_maxlifetime',86400);
ini_set('auto_detect_line_endings',true);
session_start();
$sessionid = session_id();

$askforuserinfo = false;

//check to see if accessiblity page is posting back
if (isset($_GET['launch'])) {
	//DB $query = "SELECT sessiondata,userid FROM imas_sessions WHERE sessionid='$sessionid'";
	//DB $result = mysql_query($query) or die("Query failed : " . mysql_error());
	//DB if (mysql_num_rows($result)==0) {
	$stm = $DBH->prepare("SELECT sessiondata,userid FROM imas_sessions WHERE sessionid=:sessionid");
	$stm->execute(array(':sessionid'=>$sessionid));
	if ($stm->rowCount()==0) {
		reporterror("No authorized session exists");
	}
	//DB list($enc,$userid) = mysql_fetch_row($result);
	list($enc,$userid) = $stm->fetch(PDO::FETCH_NUM);
	$sessiondata = unserialize(base64_decode($enc));
	if ($_POST['access']==1) { //text-based
		 $sessiondata['mathdisp'] = $_POST['mathdisp'];
		 $sessiondata['graphdisp'] = 0;
		 $sessiondata['useed'] = 0;
	 } else if ($_POST['access']==2) { //img graphs
		 $sessiondata['mathdisp'] = 2-$_POST['mathdisp'];
		 $sessiondata['graphdisp'] = 2;
		 $sessiondata['useed'] = 1;
	 } else if ($_POST['access']==4) { //img math
		 $sessiondata['mathdisp'] = 2;
		 $sessiondata['graphdisp'] = $_POST['graphdisp'];
		 $sessiondata['useed'] = 1;
	 } else if ($_POST['access']==3) { //img all
		 $sessiondata['mathdisp'] = 2;
		 $sessiondata['graphdisp'] = 2;
		 $sessiondata['useed'] = 1;
	 } else {
		 $sessiondata['mathdisp'] = 2-$_POST['mathdisp'];
		 $sessiondata['graphdisp'] = $_POST['graphdisp'];
		 $sessiondata['useed'] = 1;
	 }

	$enc = base64_encode(serialize($sessiondata));

	$now = time();
	//DB $query = "UPDATE imas_users SET lastaccess='$now' WHERE id='$userid'";
	//DB mysql_query($query) or die("Query failed : " . mysql_error());
	$stm = $DBH->prepare("UPDATE imas_users SET lastaccess=:lastaccess WHERE id=:id");
	$stm->execute(array(':lastaccess'=>$now, ':id'=>$userid));

	//DB $query = "UPDATE imas_sessions SET sessiondata='$enc',tzoffset='{$_POST['tzoffset']}' WHERE sessionid='$sessionid'";
	//DB mysql_query($query) or die("Query failed : " . mysql_error());
	$stm = $DBH->prepare("UPDATE imas_sessions SET sessiondata=:sessiondata,tzoffset=:tzoffset WHERE sessionid=:sessionid");
	$stm->execute(array(':sessiondata'=>$enc, ':tzoffset'=>$_POST['tzoffset'], ':sessionid'=>$sessionid));
	if (isset($cid)) {
		header("Location: http://" . $GLOBALS['basesiteurl'] . "/course/course.php?cid=$cid");
	} else {
		header("Location: http://" . $GLOBALS['basesiteurl'] . "/index.php");
	}

	exit;
} else if (isset($_GET['accessibility'])) {
	//time to output a postback to capture tzoffset and math/graph settings
	$pref = 0;
	if (isset($_COOKIE['mathgraphprefs'])) {
		 $prefparts = explode('-',$_COOKIE['mathgraphprefs']);
		 if ($prefparts[0]==2 && $prefparts[1]==2) { //img all
			$pref = 3;
		 } else if ($prefparts[0]==2) { //img math
			 $pref = 4;
		 } else if ($prefparts[1]==2) { //img graph
			 $pref = 2;
		 }
	}
	$nologo = true;
	require("../header.php");
	echo "<h4>Logging in to $installname</h4>";
	echo "<form method=\"post\" action=\"".$imasroot."/wamap/CAS/tcclaunch.php?launch=true$cidqs\" >";
	?>
	<div id="settings"><noscript>JavaScript is not enabled.  JavaScript is required for <?php echo $installname; ?>.
	Please enable JavaScript and reload this page</noscript></div>
	<input type="hidden" id="tzoffset" name="tzoffset" value="" />
	<script type="text/javascript">
		 function updateloginarea() {
			setnode = document.getElementById("settings");
			var html = "";
			html += 'Accessibility: ';
			html += "<a href='#' onClick=\"window.open('<?php echo $imasroot;?>/help.php?section=loggingin','help','top=0,width=400,height=500,scrollbars=1,left='+(screen.width-420))\">Help<\/a>";
			html += '<br/><input type="radio" name="access" value="0" <?php if ($pref==0) {echo "checked=1";} ?> />Detect my settings<br/>';
			html += '<input type="radio" name="access" value="2" <?php if ($pref==2) {echo "checked=1";} ?> />Force image-based graphs<br/>';
			html += '<input type="radio" name="access" value="4" <?php if ($pref==4) {echo "checked=1";} ?> />Force image-based math<br/>';
			html += '<input type="radio" name="access" value="3" <?php if ($pref==3) {echo "checked=1";} ?> />Force image based display<br/>';
			html += '<input type="radio" name="access" value="1">Use text-based display';

			if (AMnoMathML) {
				html += '<input type="hidden" name="mathdisp" value="0" />';
			} else {
				html += '<input type="hidden" name="mathdisp" value="1" />';
			}
			if (ASnoSVG) {
				html += '<input type="hidden" name="graphdisp" value="2" />';
			} else {
				html += '<input type="hidden" name="graphdisp" value="1" />';
			}
			html += '<div class="textright"><input type="submit" value="Login" /><\/div>';
			setnode.innerHTML = html;
			var thedate = new Date();
			document.getElementById("tzoffset").value = thedate.getTimezoneOffset();
		}
		var existingonload = window.onload;
		if (existingonload) {
			window.onload = function() {existingonload(); updateloginarea();}
		} else {
			window.onload = updateloginarea;
		}
	</script>
	</form>
	<?php
	require("../footer.php");
	exit;

} else if (isset($_GET['userinfo']) && isset($_SESSION['ltiuserid'])) {
	//check to see if new LTI user is posting back user info
	$ltiuserid = $_SESSION['ltiuserid'];
	$ltiorg = $_SESSION['ltiorg'];
	if ($_GET['userinfo']=='set') {
		//check input
		$infoerr = '';
		unset($userid);
		if ($lti_only) {
			if (empty($_POST['firstname']) || empty($_POST['lastname'])) {
				$infoerr = 'Please provide your name';
			}
			$_POST['email'] = 'none@none.com';
			$msgnot = 0;
		} else if (!isset($_REQUEST['onlyekey'])) {
			if (!empty($_POST['curSID']) && !empty($_POST['curPW'])) {
				//provided current SID/PW pair
				//DB $query = "SELECT password,id FROM imas_users WHERE SID='{$_POST['curSID']}'";
				//DB $result = mysql_query($query) or die("Query failed : " . mysql_error());
				//DB $actualpw = mysql_result($result,0,0);
				$stm = $DBH->prepare("SELECT password,id FROM imas_users WHERE SID=:SID");
				$stm->execute(array(':SID'=>$_POST['curSID']));
				list($actualpw, $thisuserid) = $stm->fetch(PDO::FETCH_NUM);
				if (password_verify($_POST['curPW'],$actualpw)) {
					//DB $userid=mysql_result($result,0,1);
					$userid=$thisuserid;
				} else {
					$infoerr = 'Existing username/password provided are not valid.';
				}
			} else {
				//new info
				if (empty($_POST['SID']) || empty($_POST['pw1']) || empty($_POST['pw2']) || empty($_POST['firstname']) || empty($_POST['lastname']) || empty($_POST['email'])) {
					$infoerr = 'Be sure to leave no requested information empty';
				} else if ($_POST['pw1'] != $_POST['pw2']) {
					$infoerr = 'Passwords don\'t match';
				} else if ($loginformat!='' && !preg_match($loginformat,$_POST['SID'])) {
					$infoerr = "$loginprompt is invalid";
				} else if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/',$_POST['email'])) {
					$infoerr = 'Invalid email address';
				} else {
					//DB $query = "SELECT id FROM imas_users WHERE SID='{$_POST['SID']}'";
					//DB $result = mysql_query($query) or die("Query failed : " . mysql_error());
					//DB if (mysql_num_rows($result)>0) {
					$stm = $DBH->prepare("SELECT id FROM imas_users WHERE SID=:SID");
					$stm->execute(array(':SID'=>$_POST['SID']));
					if ($stm->rowCount()>0) {
						$infoerr = "$loginprompt '" . Sanitize::encodeStringForDisplay($_POST['SID']) . "' already used.  Please select another.";
					}
				}
				if (isset($_POST['msgnot'])) {
					$msgnot = 1;
				} else {
					$msgnot = 0;
				}
				$md5pw = password_hash($_POST['pw1'], PASSWORD_DEFAULT);

			}
		}
		if (isset($_POST['ekey']) && $_POST['ekey']!='') {
			if (!isset($cid)) {
				$infoerr = 'Lost course id.. whoops.';
			}
			//DB $query = "SELECT enrollkey FROM imas_courses WHERE id='$cid'";
			//DB $result = mysql_query($query) or die("Query failed : " . mysql_error());
			//DB if (mysql_num_rows($result)==0) {
			$stm = $DBH->prepare("SELECT enrollkey FROM imas_courses WHERE id=:id");
			$stm->execute(array(':id'=>$cid));
			if ($stm->rowCount()==0) {
				$infoerr = "Error finding course";
			} else {
				//DB if (trim(mysql_result($result,0,0)) != trim($_POST['ekey'])) {
				if (trim($stm->fetchColumn(0)) != trim($_POST['ekey'])) {
					$infoerr = "Invalid enrollment key; try again";
				}
			}
		} else if (isset($cid)) {
			//DB $query = "SELECT enrollkey FROM imas_courses WHERE id='$cid'";
			//DB $result = mysql_query($query) or die("Query failed : " . mysql_error());
			//DB if (mysql_num_rows($result)==0) {
			$stm = $DBH->prepare("SELECT enrollkey FROM imas_courses WHERE id=:id");
			$stm->execute(array(':id'=>$cid));
			if ($stm->rowCount()==0) {
				$infoerr = "Error finding course";
			//DB } else if (trim(mysql_result($result,0,0)) != '') {
			} else if (trim($stm->fetchColumn(0)) != '') {
				$infoerr = "Invalid enrollment key; try again";
			}
		}
		if ($infoerr=='') { // no error, so create!
			if (isset($_REQUEST['onlyekey']) && !isset($_SESSION['userid'])) {
				echo "Unexpected error: lost userid, <a href=\"" . $GLOBALS['basesiteurl'] . "/wamap/CAS/tcclaunch.php?userinfo=ask$cidqs\">Try Again</a>";
				exit;
			} else if (isset($_REQUEST['onlyekey']) && isset($_SESSION['userid'])) {
				$userid = $_SESSION['userid'];
			} else {
				//DB $query = "INSERT INTO imas_ltiusers (org,ltiuserid) VALUES ('$ltiorg','$ltiuserid')";
				//DB mysql_query($query) or die("Query failed : " . mysql_error());
				//DB $localltiuser = mysql_insert_id();
				$stm = $DBH->prepare("INSERT INTO imas_ltiusers (org,ltiuserid) VALUES (:org, :ltiuserid)");
				$stm->execute(array(':org'=>$ltiorg, ':ltiuserid'=>$ltiuserid));
				$localltiuser = $DBH->lastInsertId();
				if (!isset($userid)) {
					//DB $query = "INSERT INTO imas_users (SID,password,rights,FirstName,LastName,email,msgnotify) VALUES ";
					//DB $query .= "('{$_POST['SID']}','$md5pw',10,'{$_POST['firstname']}','{$_POST['lastname']}','{$_POST['email']}',$msgnot)";
					//DB mysql_query($query) or die("Query failed : " . mysql_error());
					//DB $userid = mysql_insert_id();
					$query = "INSERT INTO imas_users (SID,password,rights,FirstName,LastName,email,msgnotify) VALUES ";
					$query .= "(:SID, :password, :rights, :FirstName, :LastName, :email, :msgnotify)";
					$stm = $DBH->prepare($query);
					$stm->execute(array(':SID'=>$_POST['SID'], ':password'=>$md5pw, ':rights'=>10, ':FirstName'=>$_POST['firstname'], ':LastName'=>$_POST['lastname'], ':email'=>$_POST['email'], ':msgnotify'=>$msgnot));
					$userid = $DBH->lastInsertId();
				}
				//DB $query = "UPDATE imas_ltiusers SET userid='$userid' WHERE id='$localltiuser'";
				//DB mysql_query($query) or die("Query failed : " . mysql_error());
				$stm = $DBH->prepare("UPDATE imas_ltiusers SET userid=:userid WHERE id=:id");
				$stm->execute(array(':userid'=>$userid, ':id'=>$localltiuser));
			}
			if (isset($cid)) {
				//DB $query = "INSERT INTO imas_students (userid,courseid) VALUES ('$userid','$cid')";
				//DB mysql_query($query) or die("Query failed : " . mysql_error());
				$stm = $DBH->prepare("INSERT INTO imas_students (userid,courseid) VALUES (:userid, :courseid)");
				$stm->execute(array(':userid'=>$userid, ':courseid'=>$cid));
			}
		} else {
			//uh-oh, had an error.  Better ask for user info again
			$askforuserinfo = true;
		}
	} else {
		//ask for student info
		$nologo = true;
		require("../../header.php");
		if (isset($infoerr)) {
			echo '<p style="color:red">'.$infoerr.'</p>';
		}
		echo "<form method=\"post\" action=\"".$imasroot."/wamap/CAS/tcclaunch.php?userinfo=set$cidqs\" ";
		if ($lti_only) {
			//using LTI for authentication; don't need username/password
			//only request name
			echo "<p>Please provide a little information about yourself:</p>";
			echo "<span class=form><label for=\"firstname\">Enter First Name:</label></span> <input class=form type=text size=20 id=firstnam name=firstname><BR class=form>\n";
			echo "<span class=form><label for=\"lastname\">Enter Last Name:</label></span> <input class=form type=text size=20 id=lastname name=lastname><BR class=form>\n";

		} else if (!isset($_GET['onlyekey'])) {
			$deffirst = '';
			$deflast = '';
			$defemail = '';

			//tying LTI to IMAthAS account
			//give option to provide existing account info, or provide full new student info
			echo "<p>If you already have an account on $installname, enter your username and ";
			echo "password below to enable automated signon from ".Sanitize::encodeStringForDisplay($ltiorgname)."</p>";
			echo "<span class=form><label for=\"curSID\">".Sanitize::encodeStringForDisplay($loginprompt).":</label></span> <input class=form type=text size=12 id=\"curSID\" name=\"curSID\"><BR class=form>\n";
			echo "<span class=form><label for=\"curPW\">Password:</label></span><input class=form type=password size=20 id=\"curPW\" name=\"curPW\"><BR class=form>\n";
			echo "<div class=submit><input type=submit value='Sign In'></div>\n";
			echo "<p>If you do not already have an account on $installname, provide the information below to create an account ";
			echo "and enable automated signon from $ltiorgname</p>";
			echo "<span class=form><label for=\"SID\">".Sanitize::encodeStringForDisplay($longloginprompt).":</label></span> <input class=form type=text size=12 id=SID name=SID><BR class=form>\n";
			echo "<span class=form><label for=\"pw1\">Choose a password:</label></span><input class=form type=password size=20 id=pw1 name=pw1><BR class=form>\n";
			echo "<span class=form><label for=\"pw2\">Confirm password:</label></span> <input class=form type=password size=20 id=pw2 name=pw2><BR class=form>\n";
			echo "<span class=form><label for=\"firstname\">Enter First Name:</label></span> <input class=form type=text value=\"".Sanitize::encodeStringForDisplay($deffirst)."\" size=20 id=firstnam name=firstname><BR class=form>\n";
			echo "<span class=form><label for=\"lastname\">Enter Last Name:</label></span> <input class=form type=text value=\"".Sanitize::encodeStringForDisplay($deflast)."\" size=20 id=lastname name=lastname><BR class=form>\n";
			echo "<span class=form><label for=\"email\">Enter E-mail address:</label></span>  <input class=form type=text value=\"".Sanitize::encodeStringForDisplay($defemail)."\" size=60 id=email name=email><BR class=form>\n";
			echo "<span class=form><label for=\"msgnot\">Notify me by email when I receive a new message:</label></span><input class=floatleft type=checkbox id=msgnot name=msgnot /><BR class=form>\n";
		} else if (isset($_GET['onlyekey'])) {
			echo '<input type="hidden" name="onlyekey" value="1" />';
		}
		if (isset($cid)) {
			//DB $query = "SELECT enrollkey FROM imas_courses WHERE id='$cid'";
			//DB $result = mysql_query($query) or die("Query failed : " . mysql_error());
			//DB if (mysql_num_rows($result)>0 && trim(mysql_result($result,0,0))!='') {
			$stm = $DBH->prepare("SELECT enrollkey FROM imas_courses WHERE id=:id");
			$stm->execute(array(':id'=>$cid));
			if ($stm->rowCount()>0 && trim($stm->fetchColumn(0))!='') {
				echo '<span class="form"><label for="ekey">Course enrollment key:</label></span><input class="form" type="text" size="12" id="ekey" name="ekey" value="" /><br class="form">';
			}
		}
		echo '<div class=submit><input type=submit value="Submit"></div>';
		echo "</form>\n";
		require("../../footer.php");
		exit;

	}

} else if (isset($_SESSION['ltiuserid']) && !isset($_POST['user_id'])) {
	//refreshed this page from accessibility options page so session already exists
	// (if user_id is set, then is new LTI request, so want to pass down to OAuth)
	//pull necessary info and continue
	//DB $query = "SELECT userid FROM imas_sessions WHERE sessionid='$sessionid'";
	//DB $result = mysql_query($query) or die("Query failed : " . mysql_error());
	//DB if (mysql_num_rows($result)==0) {
	$stm = $DBH->prepare("SELECT userid FROM imas_sessions WHERE sessionid=:sessionid");
	$stm->execute(array(':sessionid'=>$sessionid));
	if ($stm->rowCount()==0) {
		//reporterror("No session recorded");
		echo "If you haven't connected your CAS account with your WAMAP account yet, <a href=\"".$GLOBALS['basesiteurl']."/wamap/CAS/tcclaunch.php?userinfo=ask$cidqs\">Click Here</a>";
		exit;
	} else {
		//DB $userid = mysql_result($result,0,0);
		$userid = $stm->fetchColumn(0);
	}

} else {
	//not postback of new LTI user info, so must be fresh request

	//do CAS authentication check, set org userid
	include_once('CAS.php');
	phpCAS::setDebug(false);
	// initialize phpCAS
	phpCAS::client(CAS_VERSION_2_0,'my.tacomacc.edu',443,'/cas',false);

	// no SSL validation for the CAS server
	phpCAS::setNoCasServerValidation();

	if (isset($_REQUEST['logout'])) {
	  phpCAS::logout();
	}
	if (isset($_REQUEST['login'])) {
	  phpCAS::forceAuthentication();
	}

	// check CAS authentication
	$auth = phpCAS::checkAuthentication();

	$ltiuserid = phpCAS::getUser();

	//look if we know this student
	//DB $query = "SELECT userid FROM imas_ltiusers WHERE org='$ltiorg' AND ltiuserid='$ltiuserid'";
	//DB $result =  query($query) or die("Query failed : " . mysql_error());
	//DB if (mysql_num_rows($result) > 0) {
		//DB $userid = mysql_result($result,0,0);
	$stm = $DBH->prepare("SELECT userid FROM imas_ltiusers WHERE org=:org AND ltiuserid=:ltiuserid");
	$stm->execute(array(':org'=>$ltiorg, ':ltiuserid'=>$ltiuserid));
	if ($stm->rowCount() > 0) {
		$userid = $stm->fetchColumn(0);
		$_SESSION['userid'] = $userid;
		if (isset($cid)) {
			//DB $query = "(SELECT id FROM imas_students WHERE userid='$userid' AND courseid='$cid') UNION (SELECT id FROM imas_teachers WHERE userid='$userid' AND courseid='$cid')";
			//DB $result = mysql_query($query) or die("Query failed : " . mysql_error());
			//DB if (mysql_num_rows($result)==0) {
			$stm = $DBH->prepare("(SELECT id FROM imas_students WHERE userid=:userid AND courseid=:courseid) UNION (SELECT id FROM imas_teachers WHERE userid=:userid2 AND courseid=:courseid2)");
			$stm->execute(array(':userid'=>$userid, ':courseid'=>$cid, ':userid2'=>$userid, ':courseid2'=>$cid));
			if ($stm->rowCount()==0) {
				$cidqs .= '&onlyekey=true';
				//DB $query = "SELECT enrollkey FROM imas_courses WHERE id='$cid'";
				//DB $result = mysql_query($query) or die("Query failed : " . mysql_error());
				//DB if (mysql_num_rows($result)>0 && trim(mysql_result($result,0,0)) == '') {
				$stm = $DBH->prepare("SELECT enrollkey FROM imas_courses WHERE id=:id");
				$stm->execute(array(':id'=>$cid));
				if ($stm->rowCount()>0 && trim($stm->fetchColumn(0)) == '') {
					//no enrollment key, just enroll them
					header("Location: ".$GLOBALS['basesiteurl']."/wamap/CAS/tcclaunch.php?userinfo=set$cidqs");
					exit;
				}
				$askforuserinfo = true;

			}
		}
	} else {
		//student is not known.  Bummer.  Better figure out what to do with them :)

		//Store all LTI request data in session variable for reuse on submit
		//if we got this far, secret has already been verified
		$_SESSION['ltiuserid'] = $ltiuserid;
		$_SESSION['ltiorg'] = $ltiorg;

		//if doing lti_only, and first/last name were provided, go ahead and use them and don't ask
		//I don't think CAS sends name?
		/*if (count($keyparts)>2 && $keyparts[2]==1 && ((!empty($_REQUEST['lis_person_name_given']) && !empty($_REQUEST['lis_person_name_family'])) || !empty($_REQUEST['lis_person_name_full'])) ) {
			if (!empty($_REQUEST['lis_person_name_given']) && !empty($_REQUEST['lis_person_name_family'])) {
				$firstname = $_REQUEST['lis_person_name_given'];
				$lastname = $_REQUEST['lis_person_name_family'];
			} else {
				$firstname = '';
				$lastname = $_REQUEST['lis_person_name_full'];
			}
			if (!empty($_REQUEST['lis_person_contact_email_primary'])) {
				$email = $_REQUEST['lis_person_contact_email_primary'];
			} else {
				$email = 'none@none.com';
			}

			$query = "INSERT INTO imas_ltiusers (org,ltiuserid) VALUES ('$ltiorg','$ltiuserid')";
			mysql_query($query) or die("Query failed : " . mysql_error());
			$localltiuser = mysql_insert_id();
			if (!isset($userid)) {
				//make up a username/password for them
				$_POST['SID'] = 'lti-'.$localltiuser;
				$md5pw = 'pass'; //totally unusable since not md5'ed
				$query = "INSERT INTO imas_users (SID,password,rights,FirstName,LastName,email,msgnotify) VALUES ";
				$query .= "('{$_POST['SID']}','$md5pw',10,'$firstname','$lastname','$email',0)";
				mysql_query($query) or die("Query failed : " . mysql_error());
				$userid = mysql_insert_id();
			}
			$query = "UPDATE imas_ltiusers SET userid='$userid' WHERE id='$localltiuser'";
			mysql_query($query) or die("Query failed : " . mysql_error());
		} else {
		*/
			////create form asking them for user info
			$askforuserinfo = true;

		//}
	}
	//$_SESSION['ltikey'] = $ltikey;
}

//Do we need to ask for student's info?
//either first connect or bad info on first submit
if ($askforuserinfo == true) {
	if ($infoerr!='') {
		echo "error $infoerr.  <a href=\"".$GLOBALS['basesiteurl']."/wamap/CAS/tcclaunch.php?userinfo=ask$cidqs\">Try again</a>";
	} else {
		header("Location: " . $GLOBALS['basesiteurl'] . "/wamap/CAS/tcclaunch.php?userinfo=ask$cidqs");
	}
	exit;

}

//if here, we know the local userid.

$now = time();

//check if db session entry exists for session
$promptforsettings = false;
//DB $query = "SELECT userid,sessiondata FROM imas_sessions WHERE sessionid='$sessionid'";
//DB $result = mysql_query($query) or die("Query failed : " . mysql_error());
//DB if (mysql_num_rows($result)>0) {
$stm = $DBH->prepare("SELECT userid,sessiondata FROM imas_sessions WHERE sessionid=:sessionid");
$stm->execute(array(':sessionid'=>$sessionid));
if ($stm->rowCount()>0) {
	//check that same userid, and that we're not jumping on someone else's
	//existing session.  If so, then we need to create a new session.
	//DB if (mysql_result($result,0,0)!=$userid) {
	list($thisuserid,$thissessiondata) = $stm->fetch(PDO::FETCH_NUM);
	if ($thisuserid!=$userid) {
		session_regenerate_id();
		$sessionid = session_id();
		$sessiondata = array();
		$createnewsession = true;
	} else {
		//already have session.  Don't need to create one
		$sessiondata = unserialize(base64_decode($thissessiondata));
		if (!isset($sessiondata['mathdisp'])) {
			//for some reason settings are not set, so going to prompt
			$promptforsettings = true;
		}
		$createnewsession = false;
	}
} else {
	$sessiondata = array();
	$createnewsession = true;
}

$enc = base64_encode(serialize($sessiondata));
if ($createnewsession) {
	//DB $query = "INSERT INTO imas_sessions (sessionid,userid,sessiondata,time) VALUES ('$sessionid','$userid','$enc',$now)";
	$stm = $DBH->prepare("INSERT INTO imas_sessions (sessionid,userid,sessiondata,time) VALUES (:sessionid, :userid, :sessiondata, :time)");
	$stm->execute(array(':sessionid'=>$sessionid, ':userid'=>$userid, ':sessiondata'=>$enc, ':time'=>$now));
} else {
	//DB $query = "UPDATE imas_sessions SET sessiondata='$enc',userid='$userid' WHERE sessionid='$sessionid'";
	$stm = $DBH->prepare("UPDATE imas_sessions SET sessiondata=:sessiondata,userid=:userid WHERE sessionid=:sessionid");
	$stm->execute(array(':sessiondata'=>$enc, ':userid'=>$userid, ':sessionid'=>$sessionid));
}
//DB mysql_query($query) or die("Query failed : " . mysql_error());
if (!$promptforsettings && !$createnewsession) {
	//redirect now if already have session and no timelimit
	$now = time();
	//DB $query = "UPDATE imas_users SET lastaccess='$now' WHERE id='$userid'";
	//DB mysql_query($query) or die("Query failed : " . mysql_error());
	$stm = $DBH->prepare("UPDATE imas_users SET lastaccess=:lastaccess WHERE id=:id");
	$stm->execute(array(':lastaccess'=>$now, ':id'=>$userid));
	if (isset($cid)) {
		header("Location: " . $GLOBALS['basesiteurl'] . "/course/course.php?cid=$cid");
	} else {
		header("Location: " . $GLOBALS['basesiteurl'] . "/index.php");
	}
	exit;
} else {
	header("Location: " . $GLOBALS['basesiteurl'] . "/wamap/CAS/tcclaunch.php?accessibility=ask$cidqs");
	exit;
}



?>
