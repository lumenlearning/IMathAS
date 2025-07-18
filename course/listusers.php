<?php
//var_dump($_GET);
//var_dump($_POST);
//IMathAS:  Main course page
//(c) 2006 David Lippman

/*** master php includes *******/
require_once "../init.php";


/*** pre-html data manipulation, including function code *******/

//set some page specific variables and counters
$cid = Sanitize::courseId($_GET['cid']);
if (isset($_GET['secfilter'])) {
	$secfilter = $_GET['secfilter'];
	$_SESSION[$cid.'secfilter'] = $secfilter;
} else if (isset($_SESSION[$cid.'secfilter'])) {
	$secfilter = $_SESSION[$cid.'secfilter'];
} else {
	$secfilter = -1;
}
if (isset($_GET['rmode'])) {
	$rmode = $_GET['rmode'];
	$_SESSION[$cid.'rmode'] = $rmode;
} else if (isset($_SESSION[$cid.'rmode'])) {
	$rmode = $_SESSION[$cid.'rmode'];
} else {
	$rmode = 0;
}
$showemail = (($rmode&1)==1);
$showSID = (($rmode&2)==2);

$overwriteBody = 0;
$body = "";
$pagetitle = "";
$placeinhead = "";
$hasInclude = 0;
$istutor = isset($tutorid);
$isteacher = isset($teacherid);
if (!isset($CFG['GEN']['allowinstraddstus'])) {
	$CFG['GEN']['allowinstraddstus'] = true;
}
if (!isset($CFG['GEN']['allowinstraddtutors'])) {
	$CFG['GEN']['allowinstraddtutors'] = true;
}
$curBreadcrumb = $breadcrumbbase;
if (empty($_COOKIE['fromltimenu'])) {
    $curBreadcrumb .= " <a href=\"course.php?cid=$cid\">".Sanitize::encodeStringForDisplay($coursename)."</a> &gt; ";
}
if (!isset($teacherid)) { // loaded by a NON-teacher
	$overwriteBody=1;
	$body = "You need to log in as a teacher to access this page";
} else { // PERMISSIONS ARE OK, PROCEED WITH PROCESSING

	if (isset($_POST['posted']) && $_POST['posted']=="Unenroll") {
		$_GET['action'] = "unenroll";
	}
	if (isset($_POST['posted']) && $_POST['posted']=="Lock") {
		$_GET['action'] = "lock";
	}
	if (isset($_POST['lockinstead'])) {
		$_GET['action'] = "lock";
		$_POST['tolock'] = $_POST['tounenroll'] ?? $_GET['uid'];
	}

	if (isset($_GET['assigncode'])) {

		$curBreadcrumb .= " <a href=\"listusers.php?cid=$cid\">Roster</a> &gt; Assign Codes\n";
		$pagetitle = "Assign Section/Code Numbers";

		if (isset($_POST['submit'])) {
			$keys = array_keys($_POST['sec'] ?? []);
			foreach ($keys as $stuid) {
				if ($_POST['sec'][$stuid]=='') {
					$_POST['sec'][$stuid] = null;
			  }
				if ($_POST['code'][$stuid]=='') {
					$_POST['code'][$stuid] = null;
				}
            }
            require_once '../includes/setSectionGroups.php';
			foreach ($keys as $stuid) {
				$stm = $DBH->prepare("UPDATE imas_students SET section=:section,code=:code WHERE id=:id AND courseid=:courseid ");
                $stm->execute(array(':section'=>$_POST['sec'][$stuid], ':code'=>$_POST['code'][$stuid], ':id'=>$stuid, ':courseid'=>$cid));
                setSectionGroups($_POST['uid'][$stuid], $cid, $_POST['sec'][$stuid]);
			}
			header('Location: ' . $GLOBALS['basesiteurl'] . "/course/listusers.php?cid=$cid&r=" . Sanitize::randomQueryStringParam());
			exit;

		} else {
			$query = "SELECT imas_students.id,imas_students.userid,imas_users.FirstName,imas_users.LastName,imas_students.section,imas_students.code ";
			$query .= "FROM imas_students,imas_users WHERE imas_students.courseid=:courseid AND imas_students.userid=imas_users.id ";
			$query .= "ORDER BY imas_users.LastName,imas_users.FirstName";
			$resultStudentList = $DBH->prepare($query);
			$resultStudentList->execute(array(':courseid'=>$cid));
		}
	} elseif (isset($_GET['enroll']) && ($myrights==100 || (isset($CFG['GEN']['allowinstraddbyusername']) && $CFG['GEN']['allowinstraddbyusername']==true))) {

		$curBreadcrumb .= " <a href=\"listusers.php?cid=$cid\">Roster</a> &gt; Enroll Students\n";
		$pagetitle = "Enroll an Existing User";

		if (isset($_POST['username'])) {
			$stm = $DBH->prepare("SELECT id FROM imas_users WHERE SID=:SID");
			$stm->execute(array(':SID'=>$_POST['username']));
			if ($stm->rowCount()==0) {
				$overwriteBody = 1;
				$body = "Error, username doesn't exist. <a href=\"listusers.php?cid=$cid&enroll=student\">Try again</a>\n";
				if ($CFG['GEN']['allowinstraddstus']) {
					$body .= "or <a href=\"listusers.php?cid=$cid&newstu=new\">create and enroll a new student</a>";
				}
			} else {
				$id = $stm->fetchColumn(0);
				$stm = $DBH->prepare("SELECT id FROM imas_teachers WHERE userid=:userid AND courseid=:courseid");
				$stm->execute(array(':userid'=>$id, ':courseid'=>$cid));
				if ($stm->rowCount()>0) {
					echo "Teachers can't be enrolled as students - use Student View, or create a separate student account.";
					exit;
				}
				$stm = $DBH->prepare("SELECT id FROM imas_tutors WHERE userid=:userid AND courseid=:courseid");
				$stm->execute(array(':userid'=>$id, ':courseid'=>$cid));
				if ($stm->rowCount()>0) {
					echo "Tutors can't be enrolled as students.";
					exit;
				}
				$stm = $DBH->prepare("SELECT id FROM imas_students WHERE userid=:userid AND courseid=:courseid");
				$stm->execute(array(':userid'=>$id, ':courseid'=>$cid));
				if ($stm->rowCount()>0) {
					echo "This username is already enrolled in the class.";
					exit;
				}
				$stm = $DBH->prepare("SELECT deflatepass FROM imas_courses WHERE id=:id");
				$stm->execute(array(':id'=>$cid));
				$row = $stm->fetch(PDO::FETCH_NUM);
				$deflatepass = $row[0];
				$query = "INSERT INTO imas_students (userid,courseid,latepass,section,code) ";
				$query .= "VALUES (:userid,:courseid,:latepass,:section,:code)";
				$stm = $DBH->prepare($query);
				$stm->execute(array(":userid"=>$id,":courseid"=>$cid,":latepass"=>$deflatepass,
					":section"=>trim($_POST['section'])!=''?trim($_POST['section']):null,
					":code"=>trim($_POST['code'])!=''?trim($_POST['code']):null
					));
                require_once '../includes/setSectionGroups.php';
                setSectionGroups($id, $cid, $_POST['section']);
				header('Location: ' . $GLOBALS['basesiteurl'] . "/course/listusers.php?cid=$cid&r=" . Sanitize::randomQueryStringParam());
				exit;
			}

		}
	} elseif (isset($_GET['newstu']) && $CFG['GEN']['allowinstraddstus']) {
		$curBreadcrumb .= " <a href=\"listusers.php?cid=$cid\">Roster</a> &gt; Enroll Students\n";
		$pagetitle = "Enroll a New Student";
		$placeinhead = '<script type="text/javascript" src="'.$staticroot.'/javascript/jquery.validate.min.js?v=122917"></script>';

		if (isset($_POST['SID'])) {
			require_once "../includes/newusercommon.php";
			$errors = checkNewUserValidation(array('SID','firstname','lastname','email','pw1'));
			if ($errors != '') {
				$overwriteBody = 1;
				$body = $errors . "<br/><a href=\"listusers.php?cid=$cid&newstu=new\">Try Again</a>\n";
			} else {
				if (isset($CFG['GEN']['newpasswords'])) {
					require_once "../includes/password.php";
					$md5pw = password_hash($_POST['pw1'], PASSWORD_DEFAULT);
				} else {
					$md5pw = md5($_POST['pw1']);
				}
				$query = "INSERT INTO imas_users (SID, password, rights, FirstName, LastName, email, msgnotify, forcepwreset) ";
				$query .= "VALUES (:SID, :password, :rights, :FirstName, :LastName, :email, :msgnotify, 1);";
				$stm = $DBH->prepare($query);
				$stm->execute(array(':SID'=>$_POST['SID'], ':password'=>$md5pw, ':rights'=>10,
					':FirstName'=>Sanitize::stripHtmlTags($_POST['firstname']),
					':LastName'=>Sanitize::stripHtmlTags($_POST['lastname']),
					':email'=>Sanitize::emailAddress($_POST['email']),
					':msgnotify'=>0));
				$newuserid = $DBH->lastInsertId();
				//$query = "INSERT INTO imas_students (userid,courseid) VALUES ($newuserid,'$cid')";
				$stm = $DBH->prepare("SELECT deflatepass FROM imas_courses WHERE id=:id");
				$stm->execute(array(':id'=>$cid));
				$row = $stm->fetch(PDO::FETCH_NUM);
				$deflatepass = $row[0];
				$query = "INSERT INTO imas_students (userid,courseid,latepass,section,code) ";
				$query .= "VALUES (:userid,:courseid,:latepass,:section,:code)";
				$stm = $DBH->prepare($query);
				$stm->execute(array(":userid"=>$newuserid,":courseid"=>$cid,":latepass"=>$deflatepass,
					":section"=>trim($_POST['section'])!=''?trim($_POST['section']):null,
					":code"=>trim($_POST['code'])!=''?trim($_POST['code']):null
					));
                require_once '../includes/setSectionGroups.php';
                setSectionGroups($newuserid, $cid, $_POST['section']);
				header('Location: ' . $GLOBALS['basesiteurl'] . "/course/listusers.php?cid=$cid&r=" . Sanitize::randomQueryStringParam());
				exit;
			}
		}
	} elseif (isset($_POST['posted']) && $_POST['posted']=="Copy Emails") {
		$curBreadcrumb .= " <a href=\"listusers.php?cid=$cid\">Roster</a> &gt; Copy Emails\n";
		$pagetitle = "Copy Student Emails";
		if (!empty($_POST['checked'])) {
			$ulist = implode(',', array_map('intval', $_POST['checked']));
			$query = "SELECT imas_users.FirstName,imas_users.LastName,imas_users.email ";
			$query .= "FROM imas_students JOIN imas_users ON imas_students.userid=imas_users.id WHERE imas_students.courseid=:courseid AND imas_users.id IN ($ulist) ";
			$query .= "ORDER BY imas_users.LastName,imas_users.FirstName";
			$stm = $DBH->prepare($query);
			$stm->execute(array(':courseid'=>$cid));
			$stuemails = array();
			while ($row = $stm->fetch(PDO::FETCH_NUM)) {
				$row[2] = str_replace('BOUNCED','',$row[2]);
				$name = $row[0] . ' ' . $row[1];
				$stuemails[] = '"'.Sanitize::encodeStringForDisplay(str_replace('"','',$name)) . '" ' . ' &lt;' . Sanitize::encodeStringForDisplay($row[2]) . '&gt;';
			}
			$stuemails = implode('; ',$stuemails);
		}

	} elseif (isset($_GET['chgstuinfo'])) {
		$curBreadcrumb .= " <a href=\"listusers.php?cid=$cid\">Roster</a> &gt; Change User Info\n";
		$pagetitle = "Change Student Info";
		$placeinhead .= '<script type="text/javascript" src="'.$staticroot.'/javascript/jquery.validate.min.js?v=122917"></script>';
		require_once "../includes/newusercommon.php";

		if (isset($_POST['timelimitmult'])) {
			$msgout = '';
			$stm = $DBH->prepare("SELECT iu.* FROM imas_users AS iu JOIN imas_students AS istu ON istu.userid=iu.id WHERE istu.courseid=? AND istu.userid=?");
			$stm->execute([$cid, $_GET['uid']]);
			$olddata = $stm->fetch(PDO::FETCH_ASSOC);
			if ($olddata === false) {
				echo 'Invalid userid';
				exit;
			}
			$jsondata = json_decode($olddata['jsondata'], true);
			if (!is_array($jsondata)) {
				$jsondata = [];
			}
			$chglog = [];
			if (isset($_POST['SID']) && (
				$_POST['SID'] != $olddata['SID'] || $_POST['firstname'] != $olddata['FirstName'] || $_POST['lastname'] != $olddata['LastName'] ||
				$_POST['email'] != $olddata['email'] || isset($_POST['doresetpw'])
			)) {
				if (checkFormatAgainstRegex($_POST['SID'], $loginformat) && $_POST['SID'] != $olddata['SID']) {
					$un = $_POST['SID'];
					$stm = $DBH->prepare("SELECT id FROM imas_users WHERE SID=:SID");
					$stm->execute(array(':SID'=>$un));
					if ($stm->rowCount()>0) {
						$updateusername = false;
					} else {
						$updateusername = true;
						$chglog['oldusername'] = $olddata['SID'];
					}
				} else {
					$updateusername = false;
				}
				
				if ($updateusername) {
					$msgout .= '<p>Username changed to <span class="pii-username">'.Sanitize::encodeStringForDisplay($un).'</span></p>';
				} else {
					$msgout .= '<p>Username left unchanged</p>';
				}
				if ($_POST['firstname'] != $olddata['FirstName'] || $_POST['lastname'] != $olddata['LastName']) {
					$chglog['oldname'] = $olddata['LastName'] . ', ' . $olddata['FirstName'];
				}
				$query = "UPDATE imas_users SET FirstName=:FirstName,LastName=:LastName";

				$qarr = array(':FirstName'=>$_POST['firstname'], ':LastName'=>$_POST['lastname']);
				if ($updateusername) {
					$query .= ",SID=:SID";
					$qarr[':SID'] = $un;
				}
				if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/',$_POST['email']) ||
					(isset($CFG['acct']['emailFormat']) && !checkFormatAgainstRegex($_POST['email'], $CFG['acct']['emailFormat']))) {
					$msgout .= '<p>Invalid email address - left unchanged</p>';
				} else {
					if ($_POST['email'] != $olddata['email']) {
						$chglog['oldemail'] = $olddata['email'];
					}
					$query .= ",email=:email";
					$qarr[':email'] = $_POST['email'];
				}
				if (isset($_POST['doresetpw'])) {
					if (isset($CFG['acct']['passwordFormat']) && !checkFormatAgainstRegex($_POST['pw1'], $CFG['acct']['passwordFormat'])) {
						$msgout .= '<p>Invalid password - left unchanged</p>';
					} else {
						if (isset($CFG['GEN']['newpasswords'])) {
							require_once "../includes/password.php";
							$newpw = password_hash($_POST['pw1'], PASSWORD_DEFAULT);
						} else {
							$newpw = md5($_POST['pw1']);
						}
						$chglog['resetpw'] = 1;
						$query .= ",password=:password,forcepwreset=1";
						$qarr[':password'] = $newpw;
						$msgout .= '<p>Password updated</p>';
					}
				}
				if (!empty($chglog)) {
					$query .= ',jsondata=:jsondata';
					$chglog['by'] = $userid;
					$jsondata['chglog'][time()] = $chglog;
					$qarr['jsondata'] = json_encode($jsondata);
				}
				
				$query .= " WHERE id=:id AND rights<:rights";
				$qarr[':id'] = $_GET['uid'];
				$qarr[':rights'] = $myrights;
				$stm = $DBH->prepare($query);
				$stm->execute($qarr);
			} else {
				$msgout = '<p>Username, name, email, and password left unchanged.</p>';
			}
			$code = $_POST['code'];
			$section = $_POST['section'];
			if (trim($_POST['section'])==='') {
				$section = null;
			}
			if (trim($_POST['code'])==='') {
				$code = null;
			}
			if (isset($_POST['locked'])) {
				$locked = time();
			} else {
				$locked = 0;
			}
			if (isset($_POST['hidefromcourselist'])) {
				$hide = 1;
			} else {
				$hide = 0;
			}
			$timelimitmult = floatval($_POST['timelimitmult']);
			//echo $timelimitmult;
			if ($timelimitmult <= 0) {
				$timelimitmult = '1.0';
			}
			$latepasses = intval($_POST['latepasses']);
			//echo $timelimitmult;

			if ($locked==0) {
				$stm = $DBH->prepare("UPDATE imas_students SET code=:code,section=:section,locked=:locked,timelimitmult=:timelimitmult,hidefromcourselist=:hidefromcourselist,latepass=:latepass WHERE userid=:userid AND courseid=:courseid");
				$stm->execute(array(':code'=>$code, ':section'=>$section, ':locked'=>$locked, ':timelimitmult'=>$timelimitmult, ':hidefromcourselist'=>$hide, ':latepass'=>$latepasses, ':userid'=>$_GET['uid'], ':courseid'=>$cid));
			} else {
				$stm = $DBH->prepare("UPDATE imas_students SET code=:code,section=:section,timelimitmult=:timelimitmult,hidefromcourselist=:hidefromcourselist,latepass=:latepass WHERE userid=:userid AND courseid=:courseid");
				$stm->execute(array(':code'=>$code, ':section'=>$section, ':timelimitmult'=>$timelimitmult, ':hidefromcourselist'=>$hide, ':latepass'=>$latepasses, ':userid'=>$_GET['uid'], ':courseid'=>$cid));
				$stm = $DBH->prepare("UPDATE imas_students SET locked=:locked WHERE userid=:userid AND courseid=:courseid AND locked=0");
				$stm->execute(array(':locked'=>$locked, ':userid'=>$_GET['uid'], ':courseid'=>$cid));
            }
            require_once '../includes/setSectionGroups.php';
            setSectionGroups($_GET['uid'], $cid, $section);

			require_once '../includes/userpics.php';

			// $_FILES[]['tmp_name'] is not user provided. This is safe.
			if (is_uploaded_file($_FILES['stupic']['tmp_name'])) {
				processImage($_FILES['stupic'],Sanitize::onlyInt($_GET['uid']),200,200);
				processImage($_FILES['stupic'],'sm'.Sanitize::onlyInt($_GET['uid']),40,40);
				$chguserimg = 1;
			} else if (isset($_POST['removepic'])) {
				deletecoursefile('userimg_'.Sanitize::onlyInt($_GET['uid']).'.jpg');
				deletecoursefile('userimg_sm'.Sanitize::onlyInt($_GET['uid']).'.jpg');
				$chguserimg = 0;
			} else {
				$chguserimg = -1;
			}
			if ($chguserimg != -1) {
				$stm = $DBH->prepare("UPDATE imas_users SET hasuserimg=:chguserimg WHERE id=:id");
				$stm->execute(array(':id'=>$_GET['uid'], ':chguserimg'=>$chguserimg));
			}


			require_once "../header.php";
			echo '<div class="breadcrumb">'.$curBreadcrumb.'</div>';
			echo '<div id="headerlistusers" class="pagetitle"><h1>'.$pagetitle.'</h1></div>';
			echo "<p>User info updated. ";
			echo $msgout;
			echo "</p><p><a href=\"listusers.php?cid=$cid\">OK</a></p>";
			require_once "../footer.php";

			//header('Location: ' . $urlmode  . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/listusers.php?cid=$cid");
			exit;
		} else {
			$query = "SELECT imas_users.*,imas_students.code,imas_students.section,imas_students.locked,imas_students.timelimitmult,imas_students.hidefromcourselist,imas_students.latepass FROM imas_users,imas_students ";
			$query .= "WHERE imas_users.id=imas_students.userid AND imas_users.id=:id AND imas_students.courseid=:courseid";
			$stm = $DBH->prepare($query);
			$stm->execute(array(':id'=>$_GET['uid'], ':courseid'=>$cid));
			$lineStudent = $stm->fetch(PDO::FETCH_ASSOC);

		}

	} elseif ((isset($_POST['posted']) && ($_POST['posted']=="E-mail" || $_POST['posted']=="Message"))|| isset($_GET['masssend']))  {
		$calledfrom='lu';
		$overwriteBody = 1;
		$fileToInclude = "masssend.php";
	} elseif ((isset($_POST['posted']) && $_POST['posted']=="Make Exception") || isset($_GET['massexception'])) {
		$calledfrom='lu';
		$overwriteBody = 1;
		$fileToInclude = "massexception.php";
	} elseif (isset($_GET['action']) && $_GET['action']=="unenroll" && !isset($CFG['GEN']['noInstrUnenroll'])){
		$curBreadcrumb .= " <a href=\"listusers.php?cid=$cid\">Roster</a> &gt; Confirm Change\n";
		$pagetitle = "Unenroll Students";
		$calledfrom='lu';
		$overwriteBody = 1;
		$fileToInclude = "unenroll.php";

	} elseif (isset($_GET['action']) && $_GET['action']=="lock") {
		$curBreadcrumb .= " <a href=\"listusers.php?cid=$cid\">Roster</a> &gt; Confirm Change\n";
		$pagetitle = "LockStudents";
		$calledfrom='lu';
		$overwriteBody = 1;
		$fileToInclude = "lockstu.php";

	} elseif (isset($_POST['posted']) && $_POST['posted']=="Unlock") {
		$calledfrom='lu';
		require_once('lockstu.php');
		exit;
	} elseif (isset($_POST['action']) && $_POST['action']=="lockone" && is_numeric($_POST['uid'])) {
		$now = time();
		$stm = $DBH->prepare("UPDATE imas_students SET locked=:locked WHERE courseid=:courseid AND userid=:userid");
		$stm->execute(array(':locked'=>$now, ':courseid'=>$cid, ':userid'=>$_POST['uid']));

		header('Location: ' . $GLOBALS['basesiteurl'] . "/course/listusers.php?cid=$cid&r=" . Sanitize::randomQueryStringParam());
		exit;
	} elseif (isset($_POST['action']) && $_POST['action']=="unlockone" && is_numeric($_POST['uid'])) {
		$now = time();
		$stm = $DBH->prepare("UPDATE imas_students SET locked=0 WHERE courseid=:courseid AND userid=:userid");
		$stm->execute(array(':courseid'=>$cid, ':userid'=>$_POST['uid']));

		header('Location: ' . $GLOBALS['basesiteurl'] . "/course/listusers.php?cid=$cid&r=" . Sanitize::randomQueryStringParam());
		exit;
	} else { //DEFAULT DATA MANIPULATION HERE

		$curBreadcrumb .= " Roster\n";
		$pagetitle = "Student Roster";
		$stm = $DBH->prepare("SELECT DISTINCT section FROM imas_students WHERE imas_students.courseid=:courseid AND imas_students.section IS NOT NULL ORDER BY section");
		$stm->execute(array(':courseid'=>$cid));
		if ($stm->rowCount()>0) {
			$hassection = true;
			$sectionselect = "<br/><select id=\"secfiltersel\" onchange=\"chgsecfilter()\"><option value=\"-1\" " ;
			if ($secfilter==-1) {$sectionselect .= 'selected=1';}
			$sectionselect .=  '>All</option>';
			while ($row = $stm->fetch(PDO::FETCH_NUM)) {
				$sectionselect .=  "<option value=\"" . Sanitize::encodeStringForDisplay($row[0]) . "\" ";
				if ($row[0]==$secfilter) {
					$sectionselect .=  'selected=1';
				}
				$sectionselect .=  ">". Sanitize::encodeStringForDisplay($row[0]) . "</option>";
			}
			$sectionselect .=  "</select>";
		} else {
			$hassection = false;
		}
		$stm = $DBH->prepare("SELECT count(id) FROM imas_students WHERE imas_students.courseid=:courseid AND imas_students.code IS NOT NULL");
		$stm->execute(array(':courseid'=>$cid));
		if ($stm->fetchColumn(0)>0) {
			$hascode = true;
		} else {
			$hascode = false;
		}

		if ($hassection) {
			$stm = $DBH->prepare("SELECT usersort FROM imas_gbscheme WHERE courseid=:courseid");
			$stm->execute(array(':courseid'=>$cid));
			$row = $stm->fetch(PDO::FETCH_NUM);
			$sectionsort = ($row[0]==0);
		} else {
			$sectionsort = false;
		}
		$haslatepasses = false;

		$query = "SELECT imas_students.id,imas_students.userid,imas_users.FirstName,imas_users.LastName,imas_users.email,imas_users.SID,imas_students.lastaccess,";
		$query .= "imas_students.section,imas_students.code,imas_students.locked,imas_users.hasuserimg,imas_students.timelimitmult,imas_students.latepass ";
		$query .= "FROM imas_students,imas_users WHERE imas_students.courseid=:courseid AND imas_students.userid=imas_users.id ";
		if ($secfilter!=-1) {
			$query .= "AND imas_students.section=:section ";
		}
		if ($sectionsort) {
			$query .= "ORDER BY imas_students.section,imas_users.LastName,imas_users.FirstName";
		} else {
			$query .= "ORDER BY imas_users.LastName,imas_users.FirstName";
		}
		$resultDefaultUserList = $DBH->prepare($query);
		if ($secfilter!=-1) {
			$resultDefaultUserList->execute(array(':courseid'=>$cid, ':section'=>$secfilter));
		} else {
			$resultDefaultUserList->execute(array(':courseid'=>$cid));
		}
		$defaultUserList = array();
		while ($line=$resultDefaultUserList->fetch(PDO::FETCH_ASSOC)) {
			$defaultUserList[] = $line;
			if ($line['latepass']>0) {
				$haslatepasses=true;
			}
		}
		$hasSectionRowHeader = ($hassection)? "<th><label for=\"secfiltersel\">Section</label>$sectionselect</th>" : "";
		$hasCodeRowHeader = ($hascode) ? "<th>Code</th>" : "";
		$hasLatePassHeader = ($haslatepasses) ? "<th>LatePasses</th>" : "";

	}
} //END DATA MANIPULATION

//$pagetitle = "Student List";

/******* begin html output ********/
if (empty($fileToInclude)) {

$placeinhead .= "<script type=\"text/javascript\">";
$placeinhead .= 'function chgsecfilter() { ';
$placeinhead .= '       var sec = document.getElementById("secfiltersel").value; ';
$address = $GLOBALS['basesiteurl'] . "/course/listusers.php?cid=$cid";
$placeinhead .= "       var toopen = '$address&secfilter=' + encodeURIComponent(sec);\n";
$placeinhead .= "  	window.location = toopen; \n";
$placeinhead .= "}\n";
$placeinhead .= '$(function() { $(".lal").attr("title","View login log");
	$(".gl").attr("title","View student grades");
	$(".ex").attr("title","Set due date exceptions");
	$(".ui").attr("title","Edit student profile and options");
	$(".ll").attr("title","Lock student out of the course");
	$(".ull").attr("title","Allow student access to the course");
	$("input[type=checkbox]").on("change",function() {$(this).parents("tr").toggleClass("highlight");});
	});';
$placeinhead .= "</script>";
$placeinhead .= '<script type="text/javascript">$(function() {
  var html = \'<span class="dropdown"><a role="button" tabindex=0 class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">INSERTNAME <img src="'.$staticroot.'/img/collapse.gif" width="10" class="mida" alt="" /></a>\';
  html += \'<ul role="menu" class="dropdown-menu">\';
  $("a[data-uid]").each(function (i,el) {
  	var uid = $(el).attr("data-uid");
	var thishtml = html.replace("INSERTNAME", el.textContent) + \' <li><a href="listusers.php?cid=\'+cid+\'&chgstuinfo=true&uid=\'+uid+\'">'._('Student profile and options').'</a></li>\';
	thishtml += \' <li><a href="gradebook.php?cid=\'+cid+\'&stu=\'+uid+\'&from=listusers">'._('View Grades').'</a></li>\';
	thishtml += \' <li><a href="viewloginlog.php?cid=\'+cid+\'&uid=\'+uid+\'">'._('Login Log').'</a></li>\';
	thishtml += \' <li><a href="viewactionlog.php?cid=\'+cid+\'&uid=\'+uid+\'">'._('Activity Log').'</a></li>\';
	if ($(el).parent(".greystrike").length) {
		thishtml += \' <li><a href="#" onclick="postRosterForm(\'+uid+\',\\\'unlockone\\\');return false;">'._('Unlock').'</a></li>\';
	} else {
		thishtml += \' <li><a href="#" onclick="postRosterForm(\'+uid+\',\\\'lockone\\\');return false;">'._('Lock out of course').'</a></li>\';
	}
	';
if (!isset($CFG['GEN']['noInstrUnenroll'])) {
	$placeinhead .= 'thishtml += \'<li role="separator" class="divider"></li>\';';
	//$placeinhead .= 'thishtml += \'<li><a href="#" onclick="postRosterForm(\'+uid+\',\\\'unenroll\\\');;return false;">'. _('Unenroll'). '</a></li>\'';
	$placeinhead .= 'thishtml += \'<li><a href="listusers.php?cid=\'+cid+\'&action=unenroll&uid=\'+uid+\'">'. _('Unenroll'). '</a></li>\'';
}
$placeinhead .= '
	thishtml += \'</ul></span> \';
	$(el).replaceWith(thishtml);
  });
  $(".dropdown-toggle").dropdown();
  });
  function postRosterForm(uid,action) {
  	$("<form>", {method: "POST", action: $("#qform").attr("action")})
	  .append($("<input>", {name:"action", value:action, type:"hidden"}))
	  .append($("<input>", {name:"uid", value:uid, type:"hidden"}))
	  .appendTo("body").submit();
  }
  function postWithSelform(val) {
	$("#qform").append($("<input>", {name:"posted", value:val, type:"hidden"})).submit();
  }
  function copyemails() {
	var ids = [];
	$("#myTable input:checkbox:checked").each(function(i) {
		ids.push(this.value);
	});
	GB_show("Emails","viewemails.php?cid="+cid+"&ids="+ids.join("-"),500,500);
  }
  </script>';

require_once "../header.php";
$curdir = rtrim(dirname(__FILE__), '/\\');
}
/**** post-html data manipulation ******/
// this page has no post-html data manipulation

/***** page body *****/
/***** php display blocks are interspersed throughout the html as needed ****/
if ($overwriteBody==1) {
	if (strlen($body)<2) {
		require_once "./$fileToInclude";
	} else {
		echo $body;
	}
} else {
?>
	<div class=breadcrumb><?php echo $curBreadcrumb ?></div>
	<div id="headerlistusers" class="pagetitle"><h1><?php echo $pagetitle ?></h1></div>
<?php

	if (isset($_GET['assigncode'])) {
?>
	<form method=post action="listusers.php?cid=<?php echo $cid ?>&assigncode=1">
		<table class=gb>
        <caption class="sr-only">Students</caption>
			<thead>
			<tr>
				<th>Name</th><th>Section</th><th>Code</th>
			</tr>
			</thead>
			<tbody>
<?php
		$i = 0;
		while ($line=$resultStudentList->fetch(PDO::FETCH_ASSOC)) {
			$i++;
?>
			<tr>
                <td><span class="pii-full-name" id="n<?php echo $i;?>"><?php echo Sanitize::encodeStringForDisplay($line['LastName']) . ", " . Sanitize::encodeStringForDisplay($line['FirstName']); ?></span>
                    <input type="hidden" name="uid[<?php echo Sanitize::onlyInt($line['id']); ?>]" value="<?php echo Sanitize::encodeStringForDisplay($line['userid']); ?>" />
                </td>
				<td><input type=text name="sec[<?php echo Sanitize::onlyInt($line['id']); ?>]" value="<?php echo Sanitize::encodeStringForDisplay($line['section']); ?>" aria-labelledby="n<?php echo $i;?>"/></td>
				<td><input type=text name="code[<?php echo Sanitize::onlyInt($line['id']); ?>]" value="<?php echo Sanitize::encodeStringForDisplay($line['code']); ?>" aria-labelledby="n<?php echo $i;?>"/></td>
			</tr>
<?php
		}
?>
			</tbody>
		</table>
		<button type=submit name=submit value=submit>Submit</button>
	</form>
<?php
	} elseif (isset($_GET['enroll']) && ($myrights==100 || (isset($CFG['GEN']['allowinstraddbyusername']) && $CFG['GEN']['allowinstraddbyusername']==true))) {
?>
	<form method=post action="listusers.php?enroll=student&cid=<?php echo $cid ?>">
		<span class=form>Username to enroll:</span>
		<span class=formright><input type="text" class="pii-username" name="username"></span><br class=form>
		<span class=form>Section (optional):</span>
		<span class=formright><input type="text" name="section"></span><br class=form>
		<span class=form>Code (optional):</span>
		<span class=formright><input type="text" name="code"></span><br class=form>
		<div class=submit><input type="submit" value="Enroll"></div>
	</form>
<?php
	} elseif (isset($_GET['newstu']) && $CFG['GEN']['allowinstraddstus']) {
?>

	<form method=post id=pageform class=limitaftervalidate action="listusers.php?cid=<?php echo $cid ?>&newstu=new">
    <div id="errorlive" aria-live="polite" class="sr-only"></div>
	<span class=form><label for="SID"><?php echo $loginprompt;?>:</label></span> <input class="form pii-username" type=text size=12 id=SID name=SID><BR class=form>
	<span class=form><label for="pw1">Choose a password:</label></span><input class="form pii-security" type=text size=20 id=pw1 name=pw1><BR class=form>
	<span class=form><label for="firstname">Enter First Name:</label></span> <input class="form pii-first-name" type=text size=20 id=firstname name=firstname><BR class=form>
	<span class=form><label for="lastname">Enter Last Name:</label></span> <input class="form pii-last-name" type=text size=20 id=lastname name=lastname><BR class=form>
	<span class=form><label for="email">Enter E-mail address:</label></span>  <input class="form pii-email" type=text size=60 id=email name=email><BR class=form>
	<span class=form>Section (optional):</span>
		<span class=formright><input type="text" name="section"></span><br class=form>
	<span class=form>Code (optional):</span>
		<span class=formright><input type="text" name="code"></span><br class=form>
	<div class=submit><input type=submit value="Create and Enroll"></div>
	</form>

<?php
		require_once "../includes/newusercommon.php";
		showNewUserValidation("pageform");
	} elseif (isset($_POST['posted']) && $_POST['posted']=="Copy Emails") {
		if (empty($_POST['checked'])) {
			echo "No student selected. <a href=\"listusers.php?cid=$cid\">Try again</a>";
		} else {
			echo '<textarea id="emails" rows="30" cols="60">'.Sanitize::encodeStringForDisplay($stuemails).'</textarea>';
			echo '<script type="text/javascript">addLoadEvent(function(){var el=document.getElementById("emails");el.focus();el.select();})</script>';
		}

	}elseif (isset($_GET['chgstuinfo'])) {
		if ($lineStudent['rights']<$myrights) {
			$disabled = '';
		} else {
			$disabled = 'disabled';
		}
?>
		<form enctype="multipart/form-data" id=pageform method=post action="listusers.php?cid=<?php echo $cid ?>&chgstuinfo=true&uid=<?php echo Sanitize::onlyInt($_GET['uid']) ?>" class="limitaftervalidate"/>
            <div id="errorlive" aria-live="polite" class="sr-only"></div>
            <span class=form><label for="SID">User Name (login name):</label></span>
			<input <?php echo $disabled;?> class="form pii-username" type=text size=20 id=SID name=SID value="<?php echo Sanitize::encodeStringForDisplay($lineStudent['SID']); ?>"/><br class=form>
			<span class=form><label for="firstname">First Name:</label></span>
			<input <?php echo $disabled;?> class="form pii-first-name" type=text size=20 id=firstname name=firstname value="<?php echo Sanitize::encodeStringForDisplay($lineStudent['FirstName']); ?>"/><br class=form>
			<span class=form><label for="lastname">Last Name:</label></span>
			<input <?php echo $disabled;?> class="form pii-last-name" type=text size=20 id=lastname name=lastname value="<?php echo Sanitize::encodeStringForDisplay($lineStudent['LastName']); ?>"/><BR class=form>
			<span class=form><label for="email">E-mail address:</label></span>
			<input <?php echo $disabled;?> class="form pii-email" type=text size=60 id=email name=email value="<?php echo Sanitize::encodeStringForDisplay($lineStudent['email']); ?>"/><BR class=form>
			<span class=form><label for="stupic">Picture:</label></span>
			<span class="formright">
			<?php
		if ($lineStudent['hasuserimg']==1) {
			if(isset($GLOBALS['CFG']['GEN']['AWSforcoursefiles']) && $GLOBALS['CFG']['GEN']['AWSforcoursefiles'] == true) {
				echo "<img class=\"pii-image\" src=\"{$urlmode}{$GLOBALS['AWSbucket']}.s3.amazonaws.com/cfiles/userimg_" . Sanitize::onlyInt($_GET['uid']) . ".jpg\" alt=\"User picture\"/> <input type=\"checkbox\" name=\"removepic\" value=\"1\" /> Remove ";
			} else {
				$curdir = rtrim(dirname(__FILE__), '/\\');
				$galleryPath = "$curdir/course/files/";
				echo "<img class=\"pii-image\" src=\"$imasroot/course/files/userimg_" . Sanitize::onlyInt($_GET['uid']) . ".jpg\" alt=\"User picture\"/> <input type=\"checkbox\" name=\"removepic\" value=\"1\" /> Remove ";
			}
		} else {
			echo "No Pic ";
		}
		?>
			<br/><input type="file" name="stupic"/></span><br class="form" />
			<span class=form>Section (optional):</span>
			<span class=formright><input type="text" name="section" value="<?php echo Sanitize::encodeStringForDisplay($lineStudent['section']); ?>"/></span><br class=form>
			<span class=form>Code (optional):</span>
			<span class=formright><input type="text" name="code" value="<?php echo Sanitize::encodeStringForDisplay($lineStudent['code']); ?>"/></span><br class=form>
			<span class=form>Time Limit Multiplier:</span>
			<span class=formright><input type="number" min="0.01" step="0.01" name="timelimitmult" value="<?php echo Sanitize::encodeStringForDisplay($lineStudent['timelimitmult']); ?>"/></span><br class=form>
			<span class=form>LatePasses:</span>
			<span class=formright><input type="number" min="0" name="latepasses" value="<?php echo Sanitize::encodeStringForDisplay($lineStudent['latepass']); ?>"/></span><br class=form>
			<span class=form>Lock out of course?</span>
			<span class=formright><input type="checkbox" name="locked" value="1" <?php if ($lineStudent['locked']>0) {echo ' checked="checked" ';} ?>/></span><br class=form>
			<span class="form">Student has course hidden from course list?</span>
			<span class="formright"><input type="checkbox" name="hidefromcourselist" value="1" <?php if ($lineStudent['hidefromcourselist']>0) {echo ' checked="checked" ';} ?>/></span><br class=form>
			<span class=form><label for="doresetpw">Reset password?</label></span>
			<span class=formright>
				<input <?php echo $disabled;?> type=checkbox name="doresetpw" id="doresetpw" value="1" onclick="$('#newpwwrap').toggle(this.checked)" />
				<span id="newpwwrap" style="display:none"><label for="pw1">Set temporary password to:</label>
				<input type=text size=20 name="pw1" id="pw1" /></span>
			</span><br class=form />
			<div class=submit><input type=submit value="Update Info"></div>
		</form>

<?php
		$requiredrules = array(
            'pw1'=>'{depends: function(element) {return $("#doresetpw").is(":checked")}}',
            'email' => 'function(el) { return $("#SID").val().substring(0,4) != "lti-" }'
        );
		require_once "../includes/newusercommon.php";
		showNewUserValidation("pageform", array(), $requiredrules, array('originalSID'=>$lineStudent['SID']));
	} else {
?>

	<script type="text/javascript">
	var picsize = 0;
	function rotatepics() {
		picsize = (picsize+1)%3;
		picshow(picsize);
	}
	function chgpicsize() {
		var size = document.getElementById("picsize").value;
		picshow(size);
	}
	function picshow(size) {
		if (size==0) {
			els = document.getElementById("myTable").getElementsByTagName("img");
			for (var i=0; i<els.length; i++) {
				els[i].style.display = "none";
			}
		} else {
			els = document.getElementById("myTable").getElementsByTagName("img");
			for (var i=0; i<els.length; i++) {
				els[i].style.display = "inline";
				if (els[i].getAttribute("src").match("userimg_sm")) {
					if (size==2) {
						els[i].setAttribute("src",els[i].getAttribute("src").replace("_sm","_"));
					}
				} else if (size==1) {
					els[i].setAttribute("src",els[i].getAttribute("src").replace("_","_sm"));
				}
			}
		}
	}
	function chgrmode() {
		var rmode = 1*$("#showemail").val()+2*$("#showsid").val();
		window.location = "listusers.php?cid="+cid+"&rmode="+rmode;
	}
	</script>
	<script type="text/javascript" src="<?php echo $staticroot ?>/javascript/tablesorter.js"></script>
	<div class="cpmid">
	<?php
	echo '<span class="column" style="width:auto;">';
	echo "<a href=\"logingrid.php?cid=$cid\">View Login Grid</a><br/>";
	echo "<a href=\"listusers.php?cid=$cid&assigncode=1\">Assign Sections and/or Codes</a>";
	echo '</span>';
	echo '<span class="column" style="width:auto;">';
	echo "<a href=\"latepasses.php?cid=$cid\">Manage LatePasses</a>";
	if ($CFG['GEN']['allowinstraddtutors']) {
		echo "<br/><a href=\"managetutors.php?cid=$cid\">Manage Tutors</a>";
	}
    echo '</span>';
    if (empty($_COOKIE['fromltimenu'])) {
        echo '<span class="column" style="width:auto;">';
        if ($myrights==100 || (isset($CFG['GEN']['allowinstraddbyusername']) && $CFG['GEN']['allowinstraddbyusername']==true)) {
            echo "<a href=\"listusers.php?cid=$cid&enroll=student\">Enroll Student with known username</a><br/>";
        }
        echo "<a href=\"enrollfromothercourse.php?cid=$cid\">Enroll students from another course</a>";
        if (isset($CFG['GEN']['allowinstraddstus']) && $CFG['GEN']['allowinstraddstus']==true) {
            echo '</span><span class="column" style="width:auto;">';
            echo "<a href=\"$imasroot/admin/importstu.php?cid=$cid\">Import Students from File</a><br/>";
            echo "<a href=\"listusers.php?cid=$cid&newstu=new\">Create and Enroll new student</a>";
        }
        echo '</span>';
    }
    echo '<br class="clear"/>';
	echo '</div>';
	echo '<p><label>'._('Pictures').': <select id="picsize" onchange="chgpicsize()">';
	echo "<option value=0 selected>", _('None'), "</option>";
	echo "<option value=1>", _('Small'), "</option>";
	echo "<option value=2>", _('Big'), "</option></select></label> ";
	echo '<label>'._('Email').': <select id="showemail" onchange="chgrmode()">';
	echo '<option value=1 '.($showemail==true?'selected':'').'>'._('Show').'</option>';
	echo '<option value=0 '.($showemail==true?'':'selected').'>'._('Hide').'</option>';
	echo '</select></label> ';
	echo '<label>'.Sanitize::encodeStringForDisplay($loginprompt).': <select id="showsid" onchange="chgrmode()">';
	echo '<option value=1 '.($showSID==true?'selected':'').'>'._('Show').'</option>';
	echo '<option value=0 '.($showSID==true?'':'selected').'>'._('Hide').'</option>';
	echo '</select></label> ';
	echo '</p>';
	?>
	<form id="qform" method=post action="listusers.php?cid=<?php echo $cid ?>">
	<?php
	echo _('Check:'), ' <a href="#" onclick="return chkAllNone(\'qform\',\'checked[]\',true)">', _('All'), '</a> <a href="#" onclick="return chkAllNone(\'qform\',\'checked[]\',true,\'locked\')">', _('Non-locked'), '</a> <a href="#" onclick="return chkAllNone(\'qform\',\'checked[]\',false)">', _('None'), '</a> ';
	echo '<span class="dropdown">';
	echo ' <a tabindex=0 class="dropdown-toggle arrow-down" id="dropdownMenuWithsel" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
	echo _('With Selected').'</a>';
	echo '<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenuWithsel">';
	echo ' <li><a href="#" onclick="postWithSelform(\'Message\');return false;" title="',_("Send a message to the selected students"),'">', _('Message'), "</a></li>";
	if (!isset($CFG['GEN']['noEmailButton'])) {
		echo ' <li><a href="#" onclick="postWithSelform(\'E-mail\');return false;" title="',_("Send e-mail to the selected students"),'">', _('E-mail'), "</a></li>";
	}
	echo ' <li><a href="#" onclick="copyemails();return false;" title="',_("Copy e-mail addresses of the selected students"),'">', _('Copy E-mails'), "</a></li>";
	echo ' <li><a href="#" onclick="postWithSelform(\'Make Exception\');return false;" title="',_("Make due date exceptions for selected students"),'">',_('Make Exception'), "</a></li>";
	echo ' <li><a href="#" onclick="postWithSelform(\'Lock\');return false;" title="',_("Lock selected students out of the course"),'">', _('Lock'), "</a></li>";
	echo ' <li><a href="#" onclick="postWithSelform(\'Unlock\');return false;" title="',_("Un-Lock selected students from the course"),'">', _('Un-Lock'), "</a></li>";

	if (!isset($CFG['GEN']['noInstrUnenroll'])) {
		echo '<li role="separator" class="divider"></li>';
		echo ' <li><a href="#" onclick="postWithSelform(\'Unenroll\');return false;" title="',_("Unenroll the selected students"),'">', _('Unenroll'), "</a></li>";
	}

	echo '</ul></span>';
	?>
		
	<table class=gb id=myTable>
    <caption class="sr-only">Roster</caption>
		<thead>
		<tr>
			<th><span class="sr-only">Checkboxes</span></th>
			<th><span class="sr-only">Images</span></th>
			<?php echo $hasSectionRowHeader; ?>
			<?php echo $hasCodeRowHeader; ?>
			<th>Name</th>
			<th><span class="sr-only">Notes</span></th>
			<?php
			if ($showSID) {
				echo '<th>'.Sanitize::encodeStringForDisplay($loginprompt).'</th>';
			}
			if ($showemail) {
				echo '<th>'._('Email').'</th>';
			}
			?>
			<th>Last Access <span id="llt" class="sr-only">View login log</span></th>
			<th id="gt">Grades</th>
			<?php echo $hasLatePassHeader; ?>
		</tr>
		</thead>
		<tbody>
<?php
		$alt = 0;
		$numstu = 0;  $numunlocked = 0;
		$ln = 0;
		foreach ($defaultUserList as $line) {
			$ln++;
			if ($line['section']==null) {
				$line['section'] = '';
			}
			$line['email'] = str_replace('BOUNCED', '', $line['email']);
			$icons = '';
			$numstu++;
			if ($line['locked']>0) {
				$icons .= '<img src="'.$staticroot.'/img/lock.png" alt="Locked" title="Locked"/>';
			} else {
				$numunlocked++;
			}
			if ($line['timelimitmult']!=1) {
				$icons .= '<img src="'.$staticroot.'/img/time.png" alt="'._('Has a time limit multiplier set').'" title="'._('Has a time limit multiplier set').'"/> ';
			}
			if ($icons != '') {
				$icons = '<a href="listusers.php?cid='.$cid.'&chgstuinfo=true&uid='.Sanitize::onlyInt($line['userid']).'">'.$icons.'</a>';
			}

			$lastaccess = ($line['lastaccess']>0) ? tzdate("n/j/y g:ia",$line['lastaccess']) : "never";

			$hasSectionData = ($hassection) ? "<td>".Sanitize::encodeStringForDisplay($line['section'])."</td>" : "";
			$hasCodeData = ($hascode) ? "<td>".Sanitize::encodeStringForDisplay($line['code'])."</td>" : "";
			if ($alt==0) {echo "<tr class=even>"; $alt=1;} else {echo "<tr class=odd>"; $alt=0;}
?>
				<td><input type=checkbox name="checked[]" id="userchk<?php echo Sanitize::onlyInt($line['userid']); ?>" 
					value="<?php echo Sanitize::onlyInt($line['userid']); ?>" <?php if ($line['locked']>0) echo 'class="locked"'?>></td>
				<td>
<?php

	if ($line['hasuserimg']==1) {
		if(isset($GLOBALS['CFG']['GEN']['AWSforcoursefiles']) && $GLOBALS['CFG']['GEN']['AWSforcoursefiles'] == true) {
			echo "<img class=\"pii-image\" src=\"{$urlmode}{$GLOBALS['AWSbucket']}.s3.amazonaws.com/cfiles/userimg_sm" . Sanitize::onlyInt($line['userid']) . ".jpg\" style=\"display:none;\" alt=\"User picture\" />";
		} else {
			echo "<img class=\"pii-image\" src=\"$imasroot/course/files/userimg_sm" . Sanitize::onlyInt($line['userid']) . ".jpg\" style=\"display:none;\" alt=\"User picture\" />";
		}
	}
?>
				</td>
				<?php
				echo $hasSectionData;
				echo $hasCodeData;
		
				$nameline = Sanitize::encodeStringForDisplay($line['LastName']).', '.Sanitize::encodeStringForDisplay($line['FirstName']);
				//echo '<td><img data-uid="'. Sanitize::onlyInt($line['userid']) .'" src="'.$staticroot.'/img/gears.png"/> ';
				echo '<th scope=row>';
				
				if ($line['locked']>0) {
					$lineclass = 'greystrike ';
				} else {
					$lineclass = '';
				}
				echo '<label for="userchk'. Sanitize::onlyInt($line['userid']) . '" class="' . $lineclass . 'pii-full-name" id="u'.$ln.'">';
				echo '<a data-uid="'. Sanitize::onlyInt($line['userid']).'">'.$nameline.'</a></label></th>';
				echo '<td>'.$icons.'</td>';
				if ($showSID) {
					echo '<td><span class="' . $lineclass . 'pii-username">'.Sanitize::encodeStringForDisplay($line['SID']).'</span></td>';
				}
				if ($showemail) {
					echo '<td><span class="' . $lineclass . '">'.Sanitize::emailAddress($line['email']).'</span></td>';
				}
				echo '<td><span class="' . $lineclass . '"><a href="viewloginlog.php?cid='.$cid.'&uid='.Sanitize::onlyInt($line['userid']).'" class="lal" id="la'.$ln.'" aria-labelledby="la'.$ln.' llt u'.$ln.'">'.$lastaccess.'</a></span></td>';

				?>

				<td><a href="gradebook.php?cid=<?php echo $cid ?>&stu=<?php echo Sanitize::onlyInt($line['userid']); ?>&from=listusers" class="gl" aria-labelledby="gt u<?php echo $ln;?>">Grades</a></td>
				<?php
				if ($haslatepasses) {
					echo '<td>'.Sanitize::onlyInt($line['latepass']).'</td>';
				}
				?>
			</tr>
<?php
		}
?>

			</tbody>
		</table>
<?php
		echo "<p>Number of students: <b>$numunlocked</b>";
		if ($numstu != $numunlocked) {
			echo " ($numstu including locked students)";
		}
		echo '</p>';
?>

        <?php
            $sortstr = 'false,false';
            if ($hassection) {
                $sortstr .= ',"S"';
            }
            if ($hascode) {
                $sortstr .= ',"S"';
            }
            $sortstr .= ',"S"';
            $sortstr .= ',false';
            if ($showSID) {
                $sortstr .= ',"S"';
            }
            if ($showemail) {
                $sortstr .= ',"S"';
            }
            $sortstr .= ',"D"';
            $sortstr .= ',false';
            if ($haslatepasses) {
                $sortstr .= ',"N"';
            }
        ?>
		<script type="text/javascript">
			initSortTable('myTable',Array(<?php echo $sortstr;?>),true);
		</script>
	</form>



	<p></p>
<?php
	}
}

require_once "../footer.php";
?>
