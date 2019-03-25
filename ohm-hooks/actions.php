<?php

require_once("../includes/sanitize.php");


function onEnroll($courseId)
{
    if ($_GET['enrollandlogin']) {
        // Redirect the browser
        header("Location:" . $GLOBALS['basesiteurl'] . "/course/course.php?folder=0&cid=" . Sanitize::courseId($courseId));
        exit;
    }
}


function onNewUserError()
{
    global $cid, $ekey;

    if ($_POST['courseid'] && $_POST['ekey'] & $_POST['enrollandregister']) {
        $cid = Sanitize::courseId($_POST['courseid']);
        $ekey = Sanitize::encodeStringForDisplay($_POST['ekey']);
        echo "<p><a href='ohm/registerorsignin.php?cid=$cid&ekey=$ekey'>Try Again</a></p>";
    } else {
        echo '<p><a href="forms.php?action=newuser">Try Again</a></p>';
    }
}
