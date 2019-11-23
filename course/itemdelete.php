<?php
//iMathAs:  Delete course block items on course page
//2019 Alena Holligan
namespace Course;
/*** master php includes *******/
require("../init.php");

/*** pre-html data manipulation, including function code *******/
if (!(isset($teacherid))) { // loaded by a NON-teacher
    $body = "You need to log in as a teacher to access this page";
    require __DIR__ . "/views/layout.php";
    exit;
} elseif (!(isset($_GET['cid']))) {
    $body = "You need to access this page from the course page menu";
    require __DIR__ . "/views/layout.php";
    exit;
}
// PERMISSIONS ARE OK, PROCEED WITH PROCESSING
//set some page specific variables and counters
$cid = \Sanitize::courseId($_GET['cid']);
$block = \Sanitize::stripHtmlTags($_GET['block']);
$type = \Sanitize::encodeStringForDisplay($_GET['type']);
$typeid = \Sanitize::onlyInt($_GET['id']);

$itemObject = ucfirst($type) . "\\Models\\" . ucfirst($type) ."Item";
$item = new $itemObject($cid, $block);

$curBreadcrumb = "$breadcrumbbase <a href=\"course.php?cid=$item->courseid\">".\Sanitize::encodeStringForDisplay($coursename)."</a> ";
$curBreadcrumb .= "&gt; Delete $item->itemname\n";
$pagetitle = "Delete $item->itemname";

if ($_POST['remove']=="really") {
    $item->deleteItemData($typeid);
    header('Location: ' . $GLOBALS['basesiteurl'] . "/course/course.php?cid=".$cid . "&r=" . \Sanitize::randomQueryStringParam());
    exit;
}
$item->findItem($typeid);

/******* begin html output ********/
$body = __DIR__ . "/views/itemdelete.php";
require __DIR__ . "/views/layout.php";
