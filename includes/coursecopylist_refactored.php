<?php
//IMathAS:  Copy Course Items course list (Refactored Version)

if (isset($_GET['loadothergroup']) || isset($_GET['loadothers']) || isset($_POST['cidlookup'])) {
	require_once "../init.php";
}

if (!isset($myrights) || $myrights<20) {
	exit; //cannot be called directly
}

// Define constant to allow template inclusion
define('INCLUDED_FROM_COURSECOPY', true);

// Include utility functions
require_once(__DIR__ . '/coursecopy_templates/utilities.php');

/** load data **/

if (isset($_POST['cidlookup'])) {
	$query = "SELECT ic.id,ic.name,ic.enrollkey,ic.copyrights,ic.termsurl,iu.groupid,iu.LastName,iu.FirstName FROM imas_courses AS ic ";
	$query .= "JOIN imas_users AS iu ON ic.ownerid=iu.id WHERE ic.id=:id AND ic.copyrights>-1";
	$stm = $DBH->prepare($query);
	$stm->execute(array(':id'=>Sanitize::onlyInt($_POST['cidlookup'])));
	if ($stm->rowCount()==0) {
		echo '{}';
	} else {
		$row = $stm->fetch(PDO::FETCH_ASSOC);
		$out = array(
			"id"=>Sanitize::onlyInt($row['id']),
			"name"=>Sanitize::encodeStringForDisplay($row['name'] . ' ('.$row['LastName'].', '.$row['FirstName'].')'),
			"termsurl"=>Sanitize::url($row['termsurl']));
		$out['needkey'] = !($row['copyrights'] == 2 || ($row['copyrights'] == 1 && $row['groupid']==$groupid));
		echo json_encode($out, JSON_INVALID_UTF8_IGNORE);
	}
	exit;
} else if (isset($_GET['loadothers'])) {
	$stm = $DBH->query("SELECT id,name FROM imas_groups ORDER BY name");
	if ($stm->rowCount()>0) {
		$page_hasGroups=true;
		$grpnames = array();
		$grpnames[] = array('id'=>0,'name'=>_("Default Group"));
		while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
			if ($row['id']==$groupid) {continue;}
			$grpnames[] = $row;
		}
	}
	
	// Include the load others template
	include_once(__DIR__ . '/coursecopy_templates/load_others.php');

} else if (isset($_GET['loadothergroup'])) {

	$query = "SELECT ic.id,ic.name,ic.copyrights,iu.LastName,iu.FirstName,iu.email,it.userid,iu.groupid,ic.termsurl,ic.istemplate FROM imas_courses AS ic,imas_teachers AS it,imas_users AS iu  WHERE ";
	$query .= "it.courseid=ic.id AND it.userid=iu.id AND iu.groupid=:groupid AND iu.id<>:userid AND ic.available<4 AND ic.copyrights>-1 ORDER BY iu.LastName,iu.FirstName,it.userid,ic.name";
	$courseGroupResults = $DBH->prepare($query);
	$courseGroupResults->execute(array(':groupid'=>$_GET['loadothergroup'], ':userid'=>$userid));
	
	// Include the load other group template
	include_once(__DIR__ . '/coursecopy_templates/load_other_group.php');

} else {
	$stm = $DBH->prepare("SELECT jsondata FROM imas_users WHERE id=:id");
	$stm->execute(array(':id'=>$userid));
	$userjson = json_decode($stm->fetchColumn(0), true);

	$myCourseResult = $DBH->prepare("SELECT ic.id,ic.name,ic.termsurl,ic.copyrights FROM imas_courses AS ic,imas_teachers WHERE imas_teachers.courseid=ic.id AND imas_teachers.userid=:userid and ic.id<>:cid AND ic.available<4 ORDER BY ic.name");
	$myCourseResult->execute(array(':userid'=>$userid, ':cid'=>$cid));
	$myCourses = array();
	$myCoursesDefaultOrder = array();
	while ($line = $myCourseResult->fetch(PDO::FETCH_ASSOC)) {
		$myCourses[$line['id']] = $line;
		$myCoursesDefaultOrder[] = $line['id'];
	}

	$query = "SELECT ic.id,ic.name,ic.copyrights,iu.LastName,iu.FirstName,iu.email,it.userid,ic.termsurl FROM imas_courses AS ic,imas_teachers AS it,imas_users AS iu WHERE ";
	$query .= "it.courseid=ic.id AND it.userid=iu.id AND iu.groupid=:groupid AND iu.id<>:userid AND ic.available<4 AND ic.copyrights>-1 ORDER BY iu.LastName,iu.FirstName,it.userid,ic.name";
	$courseTreeResult = $DBH->prepare($query);
	$courseTreeResult->execute(array(':groupid'=>$groupid, ':userid'=>$userid));
	$lastteacher = 0;

	//$query = "SELECT ic.id,ic.name,ic.copyrights FROM imas_courses AS ic,imas_teachers WHERE imas_teachers.courseid=ic.id AND imas_teachers.userid='$templateuser' AND ic.available<4 ORDER BY ic.name";
	$courseTemplateResults = $DBH->query("SELECT id,name,copyrights,termsurl FROM imas_courses WHERE istemplate > 0 AND (istemplate&1)=1 AND copyrights=2 AND available<4 ORDER BY name");
	$query = "SELECT ic.id,ic.name,ic.copyrights,ic.termsurl FROM imas_courses AS ic JOIN imas_users AS iu ON ic.ownerid=iu.id WHERE ";
	$query .= "iu.groupid=:groupid AND ic.istemplate > 0 AND (ic.istemplate&2)=2 AND ic.copyrights>0 AND ic.available<4 ORDER BY ic.name";
	$groupTemplateResults = $DBH->prepare($query);
	$groupTemplateResults->execute(array(':groupid'=>$groupid));
	
	// Include the main course list template
	include_once(__DIR__ . '/coursecopy_templates/main_course_list.php');
}
?>
