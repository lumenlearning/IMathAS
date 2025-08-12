<?php
/**
 * Template for "My Courses" section in course copy list
 * This template displays courses that the current user teaches
 */

// Ensure this file is included, not accessed directly
if (!defined('INCLUDED_FROM_COURSECOPY')) {
    exit;
}
?>

<li class=lihdr><span class=dd>-</span>
    <span class=hdr onClick="toggle('mine')">
        <span class=btn id="bmine">+</span>
    </span>
    <span class=hdr onClick="toggle('mine')">
        <span id="nmine" ><?php echo _('My Courses'); ?></span>
    </span>
    <ul class=hide id="mine">
<?php
//my items
if (isset($userjson['courseListOrder']['teach'])) {
    $printed = array();
    printCourseOrder($userjson['courseListOrder']['teach'], $myCourses, $printed);
    $notlisted = array_diff(array_keys($myCourses), $printed);
    foreach ($notlisted as $course) {
        printCourseLine($myCourses[$course]);
    }
} else {
    foreach ($myCoursesDefaultOrder as $course) {
        printCourseLine($myCourses[$course]);
    }
}
?>
    </ul>
</li>
