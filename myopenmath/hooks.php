<?php
//MyOpenMath / WAMAP specific code hooks

//$CFG['hooks']['actions']
function getInstructorSupport($rights) {
    global $installname;
    if ($rights > 10) {
    	if ($installname == "MyOpenMath") {
            echo '<p>If you still have trouble and are an instructor, please contact support@myopenmath.com for a manual reset.</p>';
        } else if ($installname=='WAMAP') {
            echo '<p>If you still have trouble and are an instructor, please contact admin@wamap.org for a manual reset.</p>';
        }
    }
}

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
$reqFields = array(
    'school' => 'School',
    'phone' => 'Phone',
    'url' => 'Verification URL',
    'search' => 'Search'
);
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
function getDenyMessage($firstname, $lastname, $username, $groupid) {
    global $installname;
    $message = '<style type="text/css">p {margin:0 0 1em 0} </style><p>Hi '.Sanitize::encodeStringForDisplay($firstname).'</p>';
    $message .= '<p>You recently requested an instructor account on '.$installname.' with the username <b>'.Sanitize::encodeStringForDisplay($username).'</b>. ';
    $message .= 'Unfortunately, the information you provided was not sufficient for us to verify your instructor status, ';
    $message .= 'so your account has been converted to a student account. If you believe you should have an instructor account, ';
    $message .= 'you are welcome to reply to this email with additional verification information.</p>';
    $message .= '<p>If you did not use your official school email address when requesting your account, please send any additional followup <b>from your school email</b>. ';
    $message .= 'Even if we can verify your name as belonging to a teacher, we usually will not approve generic email addresses, like @yahoo.com or @gmail.com addresses, as anyone could have created that account.</p>';
    $message .= '<p>For verification, you can do any of the following:</p> <ul>';
    $message .= '<li>Provide a link to a school-maintained website listing your name. This could be a staff directory, or a class schedule. Personal blogs are not sufficient.</li>';
    $message .= '<li>Have your supervisor or HR send an email verifying your employment as faculty (that supervisor must be verifiable on a school website as well).</li>';
    $message .= '<li>Email a photo of your school employee ID identifying you as faculty.</li>';
    $message .= '</ul>';
    $message .= '<p>Thank you for your patience and support in ensuring the integrity of this resource.</p>';
    return $message;
}
function getMoreInfoMessage($firstname, $lastname, $username, $groupid) {
    global $installname;
    $message = '<style type="text/css">p {margin:0 0 1em 0} </style><p>Hi '.Sanitize::encodeStringForDisplay($firstname).'</p>';
    $message .= '<p>You recently requested an instructor account on '.$installname.' with the username <b>'.Sanitize::encodeStringForDisplay($username).'</b>. ';
    $message .= 'Unfortunately, the information you provided was not sufficient for us to verify your instructor status. ';
    if (!empty($_POST['rejreason'])) {
        if ($_POST['rejreason'] == 'badurl') {
            $message .= 'We were unable to access the verification URL provided, or we could not find you listed on the site. ';
        } else if ($_POST['rejreason'] == 'urlemail') {
            $message .= 'You made your request using a non-official email address, which was not listed on the verification URL provided. We have no way to ensure the person using this email address is actually the person listed on the website. ';
        } else if ($_POST['rejreason'] == 'badimg') {
            $message .= 'The image you provided was not readable. ';
        } else if ($_POST['rejreason'] == 'insuffimg') {
            $message .= 'The image you provided did not provide sufficient proof.  This could be because it did not list you as a teacher, or if it was something that seemed like it could be easily faked. ';
        } else if ($_POST['rejreason'] == 'missing') { 
            $message .= 'The verification information appears to have been missing. ';
        } else if ($_POST['rejreason'] == 'notWA') {
            $message .= 'It appears you are not an instructor in Washington State, and WAMAP is intended for use in Washington. You may want to look at MyOpenMath.com, WAMAP\' sister site outside Washington. ';
        }
    }
    $message .= 'If you believe you should have an instructor account, ';
    $message .= 'you are welcome to reply to this email with additional verification information.</p>';
    $message .= '<p>If you did not use your official school email address when requesting your account, please send any additional followup <b>from your school email</b>. ';
    $message .= 'Even if we can verify your name as belonging to a teacher, we usually will not approve generic email addresses, like @yahoo.com or @gmail.com addresses, as anyone could have created that account.</p>';
    $message .= '<p>For verification, you can do any of the following:</p> <ul>';
    $message .= '<li>Provide a link to a school-maintained website listing your name. This could be a staff directory, or a class schedule. Personal blogs are not sufficient.</li>';
    $message .= '<li>Have your supervisor or HR send an email verifying your employment as faculty (that supervisor must be verifiable on a school website as well).</li>';
    $message .= '<li>Email a photo of your school employee ID identifying you as faculty.</li>';
    $message .= '</ul>';
    $message .= '<p>Thank you for your patience and support in ensuring the integrity of this resource.</p>';
    return $message;
}
