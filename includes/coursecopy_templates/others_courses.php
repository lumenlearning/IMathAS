<?php
/**
 * Template for "Other's Courses" section in course copy list
 * This template displays courses from other groups and users
 */

// Ensure this file is included, not accessed directly
if (!defined('INCLUDED_FROM_COURSECOPY')) {
    exit;
}
?>

<li class=lihdr>
    <span class=dd>-</span>
    <span class=hdr onClick="toggle('other');loadothers();">
        <span class=btn id="bother">+</span>
    </span>
    <span class=hdr onClick="toggle('other');loadothers();">
        <span id="nother" ><?php echo _("Other's Courses"); ?></span>
    </span>
    <ul class=hide id="other">

<?php
//Other's courses: loaded via AHAH when clicked
echo "<li>Loading...</li>            </ul>\n        </li>\n";
?>
