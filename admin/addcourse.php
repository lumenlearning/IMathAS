<?php
//IMathAS:  First step of new course creation
//(c) 2018 David Lippman

/*** master php includes *******/
require_once "../init.php";

if ($myrights < 40) {
	echo "You don't have authorization to access this page";
	exit;
}


$placeinhead = '<script src="'.$staticroot.'/javascript/copyitemslist.js" type="text/javascript"></script>';
$placeinhead .= '<link rel="stylesheet" href="'.$staticroot.'/course/libtree.css" type="text/css" />';
$placeinhead .= '<script type="text/javascript" src="'.$staticroot.'/javascript/libtree.js"></script>';
require_once "../header.php";

echo '<div class=breadcrumb>'.$breadcrumbbase.' '._('Add New Course').'</div>';
echo '<div class="pagetitle"><h1>'._('Quick Start').'</h1></div>';

echo '<form method="POST" action="forms.php?from=home&action=addcourse">';
$dispgroup = '';
if (($myrights >= 75 || ($myspecialrights&32)==32) && isset($_GET['for']) && $_GET['for']>0 && $_GET['for'] != $userid) {
	$stm = $DBH->prepare("SELECT FirstName,LastName,groupid FROM imas_users WHERE id=?");
	$stm->execute(array($_GET['for']));
	$forinfo = $stm->fetch(PDO::FETCH_ASSOC);
	if ($myrights==100 || ($myspecialrights&32)==32 || $forinfo['groupid']==$groupid) {
		echo '<p>'._('Adding Course For').': <span class="pii-full-name">';
		echo Sanitize::encodeStringforDisplay($forinfo['LastName'].', '.$forinfo['FirstName']);
		echo '</span><input type=hidden name=for value="'.Sanitize::onlyInt($_GET['for']).'" />';
		echo '</p>';
		$dispgroup = $forinfo['groupid'];
	}
}

//Copy a template course button
if (isset($CFG['coursebrowser'])) {
	//use the course browser
	echo '<div><h2>Start with a fully-built Lumen OHM template course you can easily customize to meet the needs of your students.</h2><p>Lumen template courses are designed with evidence-based teaching practices, scaffolded for students to build a strong foundation in mathematics.</p>
	
	<button id="qa-button-copy-template" type="button" onclick="showCourseBrowser('.Sanitize::encodeStringForDisplay($dispgroup).')">';
	echo _('Use a Lumen Template');
	echo '</button>';
	echo '<input type=hidden name=coursebrowserctc id=coursebrowserctc />';
	echo '</p></div>';
}

//Copy a Course
echo '<div><h2>Copy a Course</h2>';

//Advanced options
echo '<div><h2>Advanced Options</h2>';
	echo '<div><h3>Community Templates</h3>';
	echo '<p>These are courses shared by faculty members. They are not supported by Lumen and should only be used at your own risk.</p>';
			//Copy from an existing course button
		echo '<p><button id="qa-button-copyfrom-existing-course" type="button" onclick="showCopyOpts()">';
		if (isset($CFG['addcourse']['copybutton'])) {
			echo $CFG['addcourse']['copybutton'];
		} else if (isset($CFG['coursebrowser'])) {
			// #### Begin OHM-specific code #####################################################
			// #### Begin OHM-specific code #####################################################
			// #### Begin OHM-specific code #####################################################
			// #### Begin OHM-specific code #####################################################
			// #### Begin OHM-specific code #####################################################
			echo _('Copy from an existing course');
			// #### End OHM-specific code #######################################################
			// #### End OHM-specific code #######################################################
			// #### End OHM-specific code #######################################################
			// #### End OHM-specific code #######################################################
			// #### End OHM-specific code #######################################################
		} else {
			// #### Begin OHM-specific code #####################################################
			// #### Begin OHM-specific code #####################################################
			// #### Begin OHM-specific code #####################################################
			// #### Begin OHM-specific code #####################################################
			// #### Begin OHM-specific code #####################################################
			echo _('Copy from an existing course or template');
			// #### End OHM-specific code #######################################################
			// #### End OHM-specific code #######################################################
			// #### End OHM-specific code #######################################################
			// #### End OHM-specific code #######################################################
			// #### End OHM-specific code #######################################################
		}
		echo '</button></p>';
		echo '<div id=copyoptions style="display:none; padding-left: 20px">';
		echo '<p>',_('Select a course to copy'),'</p>';
		$skipthiscourse = true;
		$cid = 0;
		require_once "../includes/coursecopylist.php";
		echo '</div>';
		writeEkeyField();
		echo '<button type=submit id=continuebutton disabled style="display:none">'._('Continue').'</button>';
		echo '</form>';
	echo '</div>';

	echo '<div><h3>Start From Scratch</h3>';
	echo '<p>Create your own course structure and content.</p>';
		echo '<button id="qa-button-add-blank-course" type="submit" name="copytype" value=0>';
		if (isset($CFG['addcourse']['blankbutton'])) {
		echo $CFG['addcourse']['blankbutton'];
		} else {
		echo _('Start with a blank course');
		}
		echo '</button>';
	echo '</div>';
echo '</div>';





