<?php
//IMathAS:  Add/modify blocks of items on course page
//(c) 2019 David Lippman

/*** master php includes *******/
require("../init.php");
require("../includes/htmlutil.php");
require_once("../includes/TeacherAuditLog.php");

/*** pre-html data manipulation, including function code *******/

//set some page specific variables and counters
$overwriteBody = 0;
$body = "";
$pagetitle = "Teacher Audit Log";
$userid = Sanitize::onlyInt($_GET['userid']);
$cid = Sanitize::courseId($_GET['cid']);

$curBreadcrumb = "$breadcrumbbase <a href=\"course.php?cid=$cid\">".Sanitize::encodeStringForDisplay($coursename)."</a> ";
$curBreadcrumb .= "&gt; Teacher Audit Log\n";

if (isset($_GET['id'])) {
	$stm = $DBH->prepare("SELECT courseid FROM imas_assessments WHERE id=?");
	$stm->execute(array(intval($_GET['id'])));
	if ($stm->rowCount()==0 || $stm->fetchColumn(0) != $_GET['cid']) {
		echo "Invalid ID";
		exit;
	}
}

if (!(isset($teacherid))) { // loaded by a NON-teacher
	$overwriteBody=1;
	$body = "You need to log in as a teacher to access this page";
} elseif (!(isset($_GET['cid']))) {
	$overwriteBody=1;
	$body = "You need to select the course";
}
function formatdate($date) {
    return tzdate("M j, Y, g:i a",strtotime($date));
}


//BEGIN DISPLAY BLOCK

 /******* begin html output ********/
//$placeinhead = "<script type=\"text/javascript\" src=\"$imasroot/javascript/DatePicker.js?v=080818\"></script>";

require("../header.php");

if ($overwriteBody==1) {
    echo $body;
} else {
    $stm = $DBH->prepare("SELECT ic.name,ic.ownerid,iu.groupid FROM imas_courses AS ic JOIN imas_users AS iu ON ic.ownerid=iu.id WHERE ic.id=?");
    $stm->execute(array($cid));
    list($coursename, $courseownerid, $coursegroupid) = $stm->fetch(PDO::FETCH_NUM);

    echo '<div class=breadcrumb>', $curBreadcrumb, '</div>';
    echo '<div id="headeruserdetail" class="pagetitle"><h1>' . _('Teacher Audit Log') . ': ';
    echo Sanitize::encodeStringForDisplay($coursename);
    echo '</h1></div>';

    $teacher_actions = TeacherAuditLog::findActionsByCourse($cid);
    $stm = $DBH->query("SELECT FirstName, LastName FROM imas_users WHERE id=".$teacher_actions[0]['userid']);
    list($first,$last) = $stm->fetch();
    echo '<table><tr>';
    echo '<th>Date/Time</th>';
    echo '<th>Teacher</th>';
    echo '<th>Action</th>';
    echo '<th>ItemID</th>';
    echo '<th>Details</th>';
    echo '</tr>';

    foreach ($teacher_actions as $action) {
        echo '<tr>';
        echo '<td>' . formatdate($action['created_at']) . '</td>';
        echo "<td>$first $last (" . Sanitize::onlyInt($action['userid']) . ')</td>';
        echo '<td>' . Sanitize::encodeStringForDisplay($action['action']) . '</td>';
        echo '<td>' . Sanitize::onlyInt($action['itemid']) . '</td>';
        echo '<td><a href="javascript:alert(\''.Sanitize::encodeStringForDisplay($action['metadata']).'\')">Details</a></td>';
        echo '</tr>';
    }
}

require("../footer.php");