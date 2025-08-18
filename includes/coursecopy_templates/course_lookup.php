<?php
/**
 * Template for "Course Lookup" section in course copy list
 * This template provides a form to lookup courses by ID
 */

// Ensure this file is included, not accessed directly
if (!defined('INCLUDED_FROM_COURSECOPY')) {
    exit;
}
?>

<p><?php echo _('Or, lookup using course ID:'); ?>
    <input type="text" size="7" id="cidlookup" />
    <button type="button" onclick="lookupcid()"><?php echo _('Look up course'); ?></button>
    <span id="cidlookupout" style="display:none;"><br/>
        <input type=radio name=ctc value=0 id=cidlookupctc />
        <span id="cidlookupname"></span>
    </span>
    <span id="cidlookuperr"></span>
</p>
