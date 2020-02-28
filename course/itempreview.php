<?php
/**
 * iMathAS: View Item Page
 *
 * @author Alena Holligan <alena@lumenlearning.com>
 */

/* master php includes */
require "../init.php";
require "../includes/htmlutil.php";
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../includes/sanitize.php');

/* pre-html data manipulation */
//set some page specific variables and counters
$cid = Sanitize::courseId($_GET['cid']);
$type = Sanitize::encodeStringForDisplay($_GET['type']);

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

if (!isset($teacherid)) {
    $body = "This " . $item->itemname . " is not currently available for viewing";
    require __DIR__ . "/views/layout.php";
}

// This is set by /desmos/js/editItem.js and is used to allow multiple
// item previews at the same time.
$previewId = $_GET['preview_id'];

/*
 * This is used by /desmos/views/edit.php to temporarily store serialized
 * Desmos form data for preview mode in /desmos/views/view.php.
 *
 * TODO: Ensure multiple browser preview tabs don't overwrite each others'
 *       temp_preview_data in $_SESSION. Need some kind of unique ID! (and cleanup?)
 */
if ('store_temp_preview_data' == $_GET['mode']) {
    if (isset($_POST['tempSerializedPreviewData']) && !empty($_POST['tempSerializedPreviewData'])) {
        $_SESSION['tempSerializedPreviewData-' . $previewId] = $_POST['tempSerializedPreviewData'];
    }
    exit;
}

// PERMISSIONS ARE OK, PROCEED WITH PROCESSING
$itemObject = ucfirst($type) . "\\Models\\" . ucfirst($type) ."Item";
$item = new $itemObject($cid);
$now = time();

$pagetitle = $item->name;
$curBreadcrumb = $breadcrumbbase;
if (!isset($sessiondata['ltiitemtype'])) {
    $curBreadcrumb .= " <a href = \"$imasroot/course/course.php?cid=$cid\">"
        . Sanitize::encodeStringForDisplay($coursename) . "</a> &gt; ";
}
if ($curBreadcrumb != '') {
    $curBreadcrumb .= $pagetitle;
}

//BEGIN DISPLAY BLOCK
/******* begin html output ********/
if ($item->typename=='desmos') {
    $placeinhead = "<script src=\"".$CFG['desmos_calculator']."\"></script>";
}
$placeinfooter = "<script src=\"$imasroot/desmos/js/setDesmos.js\"></script>";
$placeinfooter .= "<script src=\"$imasroot/desmos/js/editItem.js\"></script>";
$body = __DIR__ . "/../" . $item->typename . "/views/preview.php";
require __DIR__ . "/views/layout.php";
