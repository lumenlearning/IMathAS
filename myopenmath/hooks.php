<?php
//MyOpenMath / WAMAP specific code hooks

//$CFG['hooks']['admin/forms']
function getModGroupForm($grpid, $grptype, $myrights) {
	echo '<input type="checkbox" id="iscust" name="iscust" ';
	if ($grptype==1) { echo 'checked';}
	echo '> <label for="iscust">'._('Lumen Customer').'</label><br/>';
}

//$CFG['hooks']['admin/actions']
function onModGroup($groupid, $userid, $myrights, $usergroupid) {
	global $DBH;

	$grptype = (isset($_POST['iscust'])?1:0);
	$stm = $DBH->prepare("UPDATE imas_groups SET grouptype=:grouptype WHERE id=:id");	
	$stm->execute(array(':grouptype'=>$grptype, ':id'=>$_GET['id']));
}

//$CFG['hooks']['admin/approvepending']
function getApproveMessage($firstname, $lastname, $username, $groupid) {
	global $installname;
	$message = '<style type="text/css">p {margin:0 0 1em 0} </style><p>Hi '.Sanitize::encodeStringForDisplay($firstname).'</p>';
	if ($installname == "MyOpenMath") {
		$message .= '<p>Welcome to MyOpenMath.  Your account has been activated, and you\'re all set to log in at <a href="https://www.myopenmath.com">MyOpenMath.com</a> as an instructor using the username <b>'.Sanitize::encodeStringForDisplay($username).'</b> and the password you provided.</p>';
		$message .= '<p>I\'ve added you to the Support Course, which has forums in which you can ask questions, report problems, or find out about new system improvements.</p>';
		$message .= '<p>I\'ve also added you to the MyOpenMath Training Course.  This course has video tutorials and assignments that will walk you through learning how to use MyOpenMath in your classes.</p>';
		$message .= '<p>These will be your primary source of support; MyOpenMath does not provide individual email or phone support. We encourage you to form a learning community at your college to support each other in learning and using the system.</p>';
		$message .= '<p>Please keep in mind that MyOpenMath is run and supported by volunteers from the user community. Likewise, all courses and questions were created by your colleagues, and shared out of the kindness of their hearts. ';
		$message .= 'There is no big company or grant-funded nonprofit keeping this thing going. No one is paid by MyOpenMath to create or maintain content or provide support, ';
		$message .= 'so please be respectful of the volunteers you interact with, and consider contributing back in the future.</p>'; 
		$message .= '<p>We hope you enjoy both the cost savings and freedoms using open resources provide.</p>';
	} else if ($installname=='WAMAP') {
		$message .= 'Welcome to WAMAP.  Your account has been activated, and you\'re all set to log in as an instructor using the username <b>'.Sanitize::encodeStringForDisplay($username).'</b> and the password you provided.</p>';
		$message .= '<p>I\'ve added you to  the Support Course, which has forums in which you can ask questions, report problems, or find out about new system improvements.</p>';
		$message .= '<p>I\'ve also added you to the WAMAP Training Course.  This course has video tutorials and assignments that will walk you through learning how to use WAMAP in your classes.</p>';
		$message .= '<p>If you are from outside Washington State, please be aware that WAMAP.org is only intended for regular use by Washington State faculty.  You are welcome to use this site for trial purposes.  If you wish to continue using it, we ask you set up your own installation of the IMathAS software, or use MyOpenMath.com if using content built around an open textbook.</p>';
	}
	$message .= '<p>'.$installname.' Account Approval Volunteers</p>';
	
	return $message;
}
