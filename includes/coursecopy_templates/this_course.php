<?php
/**
 * Template for "This Course" option in course copy list
 * This template displays the current course as an option
 */

// Ensure this file is included, not accessed directly
if (!defined('INCLUDED_FROM_COURSECOPY')) {
    exit;
}

if (!isset($skipthiscourse)) {
?>
    <li><span class=dd>-</span>
        <input type=radio name=ctc value="<?php echo $cid ?>" checked=1><?php echo _('This Course'); ?></li>
<?php
}
?>
