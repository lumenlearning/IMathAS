<?php
/**
 * Template for main course list structure
 * This template provides the overall structure for the course copy list
 */

// Ensure this file is included, not accessed directly
if (!defined('INCLUDED_FROM_COURSECOPY')) {
    exit;
}
?>

<ul class=base>
<?php
// Include the "This Course" option
include_once(__DIR__ . '/this_course.php');

// Include "My Courses" section
include_once(__DIR__ . '/my_courses.php');

// Include "My Group's Courses" section
include_once(__DIR__ . '/my_group_courses.php');

// Include "Other's Courses" section
include_once(__DIR__ . '/others_courses.php');

// Include template courses
include_once(__DIR__ . '/template_courses.php');
?>
</ul>

<?php
// Include course lookup section
include_once(__DIR__ . '/course_lookup.php');
?>
