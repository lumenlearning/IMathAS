<?php
/**
 * iMathAs:  Add/modify course block items on course page
 *
 * @author Alena Holligan <alena@lumenlearning.com>
 */
namespace Course;
/*** master php includes *******/
require "../init.php";
require "../includes/htmlutil.php";
require"../includes/parsedatetime.php";
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
if (isset($_GET['tb'])) {
    $totb = \Sanitize::encodeStringForDisplay($_GET['tb']);
} else {
    $totb = 'b';
}

$itemObject = ucfirst($type) . "\\Models\\" . ucfirst($type) ."Item";
$item = new $itemObject($cid, $block, $totb);
if (isset($_GET['id'])) {
    $typeid = \Sanitize::onlyInt($_GET['id']);
    if (!$item->findItem($typeid)) {
        $body = "Invalid ID";
        require __DIR__ . "/views/layout.php";
        exit;
    }
}
// PERMISSIONS ARE OK, PROCEED WITH PROCESSING
//set some page specific variables and counters
//if the form has been submitted
if ($_POST['name']!= null || $_POST['title']!=null) {
    $fields['startdate']=parsedatetime($_POST['sdate'], $_POST['stime'], 0);
    $fields['enddate']=parsedatetime($_POST['edate'], $_POST['etime'], 2000000000);
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
    if (isset($_POST['libs'])) {
        $fields['tags'] = trim(
            str_replace(
                ',,',
                ',',
                preg_replace(
                    '/[^0-9,]/',
                    '',
                    $_POST['libs']
                )
            ),
            ','
        );
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
    if (isset($_POST['step_title'])) {
        foreach ($_POST['step_title'] as $key => $title) {
            $fields['steps'][$key] = [
                "title" => $title,
                "text" => $_POST['step_text'][$key],
                "id" => $_POST['step'][$key],
            ];
        }
    }
    if (isset($typeid)) {  //already have id; update
        $item->updateItem($typeid, $fields);
        $track_type = $item->track('edit');
    } else { //add new
        $fields['courseid'] = $cid;
        $item = new $itemObject($cid, $block, $totb);
        $item->addItem($fields);
        $track_type = $item->track('add');
    }
    header(
        'Location: ' . $GLOBALS['basesiteurl']
        . "/course/course.php?cid=$item->courseid&r="
        .\Sanitize::randomQueryStringParam()
    );
    exit;
}
if (isset($typeid)) {
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
        $gradeoutcomes = explode(',', $item->outcomes);
    } else {
        $gradeoutcomes = array();
    }
    $savetitle = _("Save Changes");
} else {
    //set defaults
    $item->setAvail(1);
    $item->setStartDate(time());
    $item->setEndDate(time() + 7*24*60*60);
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
    $sdate = tzdate("m/d/Y", $item->startdate);
    $stime = tzdate("g:i a", $item->startdate);
} else {
    $sdate = tzdate("m/d/Y", time());
    $stime = $defstime; //tzdate("g:i a",time());
}
if ($item->enddate!=2000000000) {
    $edate = tzdate("m/d/Y", $item->enddate);
    $etime = tzdate("g:i a", $item->enddate);
} else {
    $edate = tzdate("m/d/Y", time()+7*24*60*60);
    $etime = $deftime; //tzdate("g:i a",time()+7*24*60*60);
}
if (!isset($typeid)) {
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
if (isset($typeid)) {
    $page_actionArray['id'] = $typeid;
}
$page_formActionTag = "itemadd.php?" . \Sanitize::generateQueryStringFromMap(
    $page_actionArray
);

$curBreadcrumb = "$breadcrumbbase <a href=\"$imasroot/course/course.php?cid=$cid\">"
    .\Sanitize::encodeStringForDisplay($coursename)."</a> ";
if (isset($_GET['id'])) {  //already have id; update
    $curBreadcrumb .= "&gt; Modify " . $item->itemname . "\n";
    $pagetitle = "Modify " . $item->itemname;
} else {
    $curBreadcrumb .= "&gt; Add " . $item->itemname . "\n";
    $pagetitle = "Add " . $item->itemname;
}
/******* begin html output ********/
// Use TinyMCE for Desmos items.
$useeditor = 'noinit';
$placeinhead = '<script type="text/javascript">
    var numsteps = '.count($item->steps).';
	$(function() {
	    desmos = " desmos ";
		initeditor("selector","textarea");
	});
	</script>';

if ($item->typename=='desmos') {
    //$placeinhead .= "<script src=\"".$CFG['desmos_calculator']."\"></script>";
}
$placeinhead .= "<script type=\"text/javascript\" src=\"$imasroot/javascript/addquestions.js\"></script>";
$placeinhead .= "<script type=\"text/javascript\" src=\"$imasroot/javascript/DatePicker.js\"></script>";
$placeinhead .= "<link title='lux' rel=\"stylesheet\" type=\"text/css\" href=\"$imasroot/themes/lux-temp.css\">";
$placeinfooter = "<script src=\"$imasroot/desmos/js/setDesmos.js\"></script>";
$placeinfooter .= "<script src=\"$imasroot/desmos/js/editItem.js\"></script>";
$body = __DIR__ . "/../" . $item->typename . "/views/edit.php";
require __DIR__ . "/views/layout.php";
