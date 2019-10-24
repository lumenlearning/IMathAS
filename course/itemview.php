<?php
//iMathAS: View Item Page
//2019 Alena Holligan

/*** master php includes *******/
require("../init.php");
require("../includes/htmlutil.php");
/*** pre-html data manipulation *******/
//set some page specific variables and counters
$cid = \Sanitize::courseId($_GET['cid']);
$id = \Sanitize::onlyInt($_GET['id']);
$type = \Sanitize::encodeStringForDisplay($_GET['type']);

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
$itemObject = ucfirst($type) . "\\Models\\" . ucfirst($type) ."Item";
$item = new $itemObject($cid);
$item->findItem($id);
$now = time();
if (
    !isset($teacherid) && (
        $item->avail==0 || (
            $item->avail==1 && (
                $now < $item->startdate || $now > $item->enddate
            )
        )
    )
) {
    $body = "This " . $item->display_name . " is not currently available for viewing";
    require __DIR__ . "/views/layout.php";
    exit;
}
$pagetitle = $item->name;
$curBreadcrumb = "$breadcrumbbase <a href=\"$imasroot/course/course.php?cid=$cid\">"
    . \Sanitize::encodeStringForDisplay($coursename)."</a>"
    . " &gt; " . $item->display_name;
//BEGIN DISPLAY BLOCK
/******* begin html output ********/
$body = __DIR__ . "/../" . $item->typename . "/views/view.php";
require __DIR__ . "/views/layout.php";