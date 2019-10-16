<?php
//OHM:  Add/modify desmos block items on course page
//(c) 2019 Alena Holligan
namespace Desmos;
use OHM\Includes\CourseItems;
use Desmos\Models\DesmosInteractive;
/*** master php includes *******/
require("../init.php");
require("../includes/htmlutil.php");
require("../includes/parsedatetime.php");
if (!(isset($teacherid))) { // loaded by a NON-teacher
    $body = "You need to log in as a teacher to access this page";
    require __DIR__ . "/views/layout.php";
    exit;
}
if (!(isset($_GET['cid']))) {
    $body = "You need to access this page from the course page menu";
    require __DIR__ . "/views/layout.php";
    exit;
}
$cid = \Sanitize::courseId($_GET['cid']);
$block = \Sanitize::encodeStringForDisplay($_GET['block']);
$desmos = new DesmosInteractive($cid);
if (isset($_GET['id'])) {
    $desmosid = \Sanitize::onlyInt($_GET['id']);
    if (!$desmos->getItem($desmosid)) {
        $body = "Invalid ID";
        require __DIR__ . "/views/layout.php";
        exit;
    }
}
// PERMISSIONS ARE OK, PROCEED WITH PROCESSING
//set some page specific variables and counters
$curBreadcrumb = "$breadcrumbbase <a href=\"$imasroot/course/course.php?cid=$cid\">".\Sanitize::encodeStringForDisplay($coursename)."</a> ";
if (isset($_GET['id'])) {  //already have id; update
    $curBreadcrumb .= "&gt; Modify Desmos Interactive\n";
    $pagetitle = "Modify Desmos Interactive";
} else {
    $curBreadcrumb .= "&gt; Add Desmos Interactive\n";
    $pagetitle = "Add Desmos Interactive";
}
if (isset($_GET['tb'])) {
    $totb = \Sanitize::encodeStringForDisplay($_GET['tb']);
} else {
    $totb = 'b';
}
$useeditor = "desmos";
$page_formActionTag = "adddesmos.php?" . \Sanitize::generateQueryStringFromMap(
        array('block' => $block, 'cid' => $cid)
    );
$page_formActionTag .= "&tb=$totb";
if ($_POST['title']!= null || $_POST['summary']!=null || $_POST['sdate']!=null) { //if the form has been submitted
    if ($_POST['avail']==1) {
        if ($_POST['sdatetype']=='0') {
            $startdate = 0;
        } else {
            $startdate = parsedatetime($_POST['sdate'],$_POST['stime'],0);
        }
        if ($_POST['edatetype']=='2000000000') {
            $enddate = 2000000000;
        } else {
            $enddate = parsedatetime($_POST['edate'],$_POST['etime'],2000000000);
        }
        $oncal = \Sanitize::onlyInt($_POST['oncal']);
    } else if ($_POST['avail']==2) {
        if ($_POST['altoncal']==0) {
            $startdate = 0;
            $oncal = 0;
        } else {
            $startdate = parsedatetime($_POST['cdate'],"12:00 pm",0);
            $oncal = ($startdate>0)?1:0;
            $caltag = \Sanitize::stripHtmlTags($_POST['altcaltag']);
        }
        $enddate =  2000000000;
    }else {
        $startdate = 0;
        $enddate = 2000000000;
        $oncal = 0;
    }
    if (isset($_POST['hidetitle'])) {
        $_POST['title']='##hidden##';
    }
    if (isset($_POST['isplaylist'])) {
        $isplaylist = 1;
    } else {
        $isplaylist = 0;
    }
    $_POST['title'] = \Sanitize::stripHtmlTags($_POST['title']);
    $_POST['summary'] = \Sanitize::incomingHtml($_POST['summary']);
    $outcomes = array();
    if (isset($_POST['outcomes'])) {
        foreach ($_POST['outcomes'] as $o) {
            if (is_numeric($o) && $o>0) {
                $outcomes[] = intval($o);
            }
        }
    }
    $outcomes = implode(',',$outcomes);
    $fields = [
        'title' => $_POST['title'],
        'summary' => $_POST['summary'],
        'startdate' => $startdate,
        'enddate' => $enddate,
        'avail' => \Sanitize::onlyInt($_POST['avail']),
    ];
    if (isset($desmosid)) {  //already have id; update
        $desmos->updateItem($desmosid, $fields);
    } else { //add new
        $fields['courseid'] = $cid;
        $courseitems = new CourseItems($cid, $block, $totb);
        $desmosid  = $desmos->addItem($fields, $courseitems);
    }
}
if ($_POST['submitbtn']=='Submit') {
    header('Location: ' . $GLOBALS['basesiteurl'] . "/course/course.php?cid=".\Sanitize::courseId($_GET['cid']) ."&r=" .\Sanitize::randomQueryStringParam());
    exit;
}
if (isset($desmosid)) {
    $line = $desmos->getItem($desmosid);
    if ($line['title']=='##hidden##') {
        $hidetitle = true;
        $line['title']='';
    }
    $startdate = $line['startdate'];
    $enddate = $line['enddate'];
    if ($line['avail']==2 && $startdate>0) {
        $altoncal = 1;
    } else {
        $altoncal = 0;
    }
    if ($line['outcomes']!='') {
        $gradeoutcomes = explode(',',$line['outcomes']);
    } else {
        $gradeoutcomes = array();
    }
    $savetitle = _("Save Changes");
} else {
    //set defaults
    $line['title'] = "";
    $line['summary'] = "";
    $line['avail'] = 1;
    $line['oncal'] = 0;
    $line['caltag'] = '!';
    $altoncal = 0;
    $startdate = time();
    $enddate = time() + 7*24*60*60;
    $hidetitle = false;
    $gradeoutcomes = array();
    $savetitle = _("Create Item");
}
$hr = floor($coursedeftime/60)%12;
$min = $coursedeftime%60;
$am = ($coursedeftime<12*60)?'am':'pm';
$deftime = (($hr==0)?12:$hr).':'.(($min<10)?'0':'').$min.' '.$am;
$hr = floor($coursedefstime/60)%12;
$min = $coursedefstime%60;
$am = ($coursedefstime<12*60)?'am':'pm';
$defstime = (($hr==0)?12:$hr).':'.(($min<10)?'0':'').$min.' '.$am;
if ($startdate!=0) {
    $sdate = tzdate("m/d/Y",$startdate);
    $stime = tzdate("g:i a",$startdate);
} else {
    $sdate = tzdate("m/d/Y",time());
    $stime = $defstime; //tzdate("g:i a",time());
}
if ($enddate!=2000000000) {
    $edate = tzdate("m/d/Y",$enddate);
    $etime = tzdate("g:i a",$enddate);
} else {
    $edate = tzdate("m/d/Y",time()+7*24*60*60);
    $etime = $deftime; //tzdate("g:i a",time()+7*24*60*60);
}
if (!isset($desmosid)) {
    $stime = $defstime;
    $etime = $deftime;
}
$outcomenames = array();
$stm = $DBH->prepare("SELECT id,name FROM imas_outcomes WHERE courseid=:courseid");
$stm->execute(array(':courseid'=>$cid));
while ($row = $stm->fetch(\PDO::FETCH_NUM)) {
    $outcomenames[$row[0]] = $row[1];
}
$stm = $DBH->prepare("SELECT outcomes FROM imas_courses WHERE id=:id");
$stm->execute(array(':id'=>$cid));
$row = $stm->fetch(\PDO::FETCH_NUM);
if ($row[0]=='') {
    $outcomearr = array();
} else {
    $outcomearr = unserialize($row[0]);
    if ($outcomearr===false) {
        $outcomearr = array();
    }
}
$outcomes = array();
function flattenarr($ar) {
    global $outcomes;
    foreach ($ar as $v) {
        if (is_array($v)) { //outcome group
            $outcomes[] = array($v['name'], 1);
            flattenarr($v['outcomes']);
        } else {
            $outcomes[] = array($v, 0);
        }
    }
}
flattenarr($outcomearr);
$page_formActionTag .= (isset($desmosid)) ? "&id=" . $desmosid : "";
/******* begin html output ********/
$placeinhead = "<script type=\"text/javascript\" src=\"$imasroot/javascript/DatePicker.js\"></script>";
$body = __DIR__ . "/views/edit.php";
require __DIR__ . "/views/layout.php";