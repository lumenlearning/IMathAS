<?php
//OHM:  View Desmos page
//(c) 2019 Alena Holligan
namespace Desmos;
use Desmos\Models\DesmosInteractive;
/*** master php includes *******/
require("../init.php");
require("../includes/htmlutil.php");
/*** pre-html data manipulation, including function code *******/
//set some page specific variables and counters
$cid = \Sanitize::onlyInt($_GET['cid']);
$id = \Sanitize::onlyInt($_GET['id']);
if (isset($_GET['framed'])) {
    $flexwidth = true;
    $shownav = false;
    $framed = "&framed=true";
} else {
    $shownav = true;
    $framed = '';
}
if ($cid==0) {
    $body = "You need to access this page with a course id";
    $body .= "<p><a href=\"$imasroot/course/course.php?cid=$cid$framed\">Back</a></p>";
    require __DIR__ . "/views/layout.php";
    exit;
}
if ($id==0) {
    $body = "You need to access this page with a wiki id";
    $body .= "<p><a href=\"$imasroot/course/course.php?cid=$cid$framed\">Back</a></p>";
    require __DIR__ . "/views/layout.php";
    exit;
}
// PERMISSIONS ARE OK, PROCEED WITH PROCESSING
$desmos = new DesmosInteractive($cid);
$row = $desmos->getItem($id);
$now = time();
if (!isset($teacherid) && ($row['avail']==0 || ($row['avail']==1 && ($now<$row['startdate'] || $now>$row['enddate'])))) {
    $body = "This Desmos Interactive is not currently available for viewing";
    require __DIR__ . "/views/layout.php";
    exit;
}
$pagetitle = $row['title'];
$curBreadcrumb = "$breadcrumbbase <a href=\"$imasroot/course/course.php?cid=$cid\">"
    . \Sanitize::encodeStringForDisplay($coursename)."</a>"
    . " &gt; View Desmos Interactive";
//BEGIN DISPLAY BLOCK
/******* begin html output ********/
$placeinhead = '<script type="text/javascript" src="'.$imasroot.'/javascript/viewwiki.js?v=051710"></script>';
$body = __DIR__ . "/views/view.php";
require __DIR__ . "/views/layout.php";