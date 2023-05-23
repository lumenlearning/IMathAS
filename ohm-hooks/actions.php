<?php

require_once(__DIR__ . "/../includes/sanitize.php");


/**
 * Called when a user enrolls in a course.
 *
 * @param int $courseId The course ID the user is enrolling in.
 */
function onEnroll($courseId)
{
    if ($_GET['enrollandlogin']) {
        header(sprintf('Location: %s/course/course.php?folder=0&cid=%d',
            $GLOBALS['basesiteurl'], Sanitize::courseId($courseId)));
        exit;
    }
}


/**
 * Called when an error is generated during new user account creation.
 */
function onNewUserError()
{
    if ($_POST['courseid'] && $_POST['ekey'] && $_POST['enrollandregister']) {
        printf("<p><a href='ohm/registerorsignin.php?cid=%d&ekey=%s'>Try Again</a></p>",
            Sanitize::courseId($_POST['courseid']),
            Sanitize::encodeStringForDisplay($_POST['ekey']));
    } else {
        echo '<p><a href="forms.php?action=newuser">Try Again</a></p>';
    }
}
