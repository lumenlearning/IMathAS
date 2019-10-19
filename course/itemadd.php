<?php
//iMathAs:  Add/modify course block items on course page
//2019 Alena Holligan
namespace Course;
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
$type = \Sanitize::encodeStringForDisplay($_GET['type']);

$itemObject = ucfirst($type) . "\\Models\\" . ucfirst($type) ."Item";
$item = new $itemObject($cid, $block);
if (isset($_GET['id'])) {
    $itemid = \Sanitize::onlyInt($_GET['id']);
    if (!$item->getItem($itemid)) {
        $body = "Invalid ID";
        require __DIR__ . "/views/layout.php";
        exit;
    }
}
// PERMISSIONS ARE OK, PROCEED WITH PROCESSING
//set some page specific variables and counters
if (isset($_GET['tb'])) {
    $totb = \Sanitize::encodeStringForDisplay($_GET['tb']);
} else {
    $totb = 'b';
}
$useeditor = "desmos";
if ($_POST['name']!= null || $_POST['title']!=null) { //if the form has been submitted
    if ($_POST['avail']==1) {
        if ($_POST['sdatetype']=='0') {
            $fields['startdate'] = 0;
        } else {
            $fields['startdate'] = parsedatetime($_POST['sdate'],$_POST['stime'],0);
        }
        if ($_POST['edatetype']=='2000000000') {
            $fields['enddate'] = 2000000000;
        } else {
            $fields['enddate'] = parsedatetime($_POST['edate'],$_POST['etime'],2000000000);
        }
        if ($_POST['oncal']) {
            $fields['oncal'] = \Sanitize::onlyInt($_POST['oncal']);
        }
    } else if ($_POST['avail']==2) {
        if ($_POST['altoncal']==0) {
            $fields['startdate'] = 0;
            //$fields['oncal'] = 0;
        } else {
            $fields['startdate'] = parsedatetime($_POST['cdate'],"12:00 pm",0);
            //$fields['oncal'] = ($fields['startdate']>0)?1:0;
            //$fields['caltag'] = \Sanitize::stripHtmlTags($_POST['altcaltag']);
        }
        $fields['enddate'] =  2000000000;
    }else {
        $fields['startdate'] = 0;
        $fields['enddate'] = 2000000000;
        //$fields['oncal'] = 0;
    }
    if (isset($_POST['hidetitle'])) {
        $_POST['title']='##hidden##';
    }
    if (isset($_POST['isplaylist'])) {
        $fields['isplaylist'] = 1;
    } else {
        //$fields['isplaylist'] = 0;
    }
    if (isset($_POST['title'])) {
        $fields['title'] = \Sanitize::stripHtmlTags($_POST['title']);
    }
    if (isset($_POST['name'])) {
        $fields['name'] = \Sanitize::stripHtmlTags($_POST['name']);
    }
    if (isset($_POST['summary'])) {
        $fields['summary'] = \Sanitize::incomingHtml($_POST['summary']);
    }
    if (isset($_POST['description'])) {
        $fields['description'] = \Sanitize::incomingHtml($_POST['description']);
    }
    $outcomes = array();
    if (isset($_POST['outcomes'])) {
        foreach ($_POST['outcomes'] as $o) {
            if (is_numeric($o) && $o>0) {
                $outcomes[] = intval($o);
            }
        }
    }
    $fields['outcomes'] = implode(',', $outcomes);
    if (isset($itemid)) {  //already have id; update
        $item->updateItem($itemid, $fields);
    } else { //add new
        $fields['courseid'] = $cid;
        $item = new $itemObject($cid, $block, $totb);
        $itemid  = $item->addItem($fields);
    }
    header('Location: ' . $GLOBALS['basesiteurl'] . "/course/course.php?cid=".\Sanitize::courseId($_GET['cid']) ."&r=" .\Sanitize::randomQueryStringParam());
    exit;
}
if (isset($itemid)) {
    $item->getItem($itemid);
    if ($item->name=='##hidden##' || $item->title=='##hidden##') {
        $hidetitle = true;
        $item->name='';
        $item->title='';
    }
    if ($item->avail == 2 && $item->startdate > 0) {
        $altoncal = 1;
    } else {
        $altoncal = 0;
    }
    if ($item->outcomes != '') {
        $gradeoutcomes = explode(',',$item->outcomes);
    } else {
        $gradeoutcomes = array();
    }
    $savetitle = _("Save Changes");
} else {
    //set defaults
    $item->avail = 1;
    $item->startdate = time();
    $item->enddate = time() + 7*24*60*60;
    $altoncal = 0;
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
if ($item->startdate!=0) {
    $sdate = tzdate("m/d/Y",$item->startdate);
    $stime = tzdate("g:i a",$item->startdate);
} else {
    $sdate = tzdate("m/d/Y",time());
    $stime = $defstime; //tzdate("g:i a",time());
}
if ($item->enddate!=2000000000) {
    $edate = tzdate("m/d/Y",$item->enddate);
    $etime = tzdate("g:i a",$item->enddate);
} else {
    $edate = tzdate("m/d/Y",time()+7*24*60*60);
    $etime = $deftime; //tzdate("g:i a",time()+7*24*60*60);
}
if (!isset($itemid)) {
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

$page_actionArray = array('type' => $type, 'block' => $block, 'cid' => $cid);
$page_actionArray['tb'] = $totb;
if (isset($itemid)) {
    $page_actionArray['id'] = $itemid;
}
$page_formActionTag = "itemadd.php?" . \Sanitize::generateQueryStringFromMap(
        $page_actionArray
    );

$curBreadcrumb = "$breadcrumbbase <a href=\"$imasroot/course/course.php?cid=$cid\">".\Sanitize::encodeStringForDisplay($coursename)."</a> ";
if (isset($_GET['id'])) {  //already have id; update
    $curBreadcrumb .= "&gt; Modify " . $item->display_name . "\n";
    $pagetitle = "Modify " . $item->display_name;
} else {
    $curBreadcrumb .= "&gt; Add " . $item->display_name . "\n";
    $pagetitle = "Add " . $item->display_name;
}
/******* begin html output ********/
$placeinhead = "<script type=\"text/javascript\" src=\"$imasroot/javascript/DatePicker.js\"></script>";
$body = __DIR__ . "/../" . $item->typename . "/views/edit.php";
require __DIR__ . "/views/layout.php";