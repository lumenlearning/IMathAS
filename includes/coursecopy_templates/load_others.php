<?php
/**
 * Template for loading other groups via AJAX
 * This template handles the loadothers GET parameter
 */

// Ensure this file is included, not accessed directly
if (!defined('INCLUDED_FROM_COURSECOPY')) {
    exit;
}

if ($page_hasGroups) {
    foreach ($grpnames as $grp) {
        ?>
                    <li class=lihdr>
                        <span class=dd>-</span>
                        <span class=hdr onClick="loadothergroup('<?php echo Sanitize::encodeStringForJavascript($grp['id']); ?>')">
                            <span class=btn id="bg<?php echo Sanitize::encodeStringForDisplay($grp['id']); ?>">+</span>
                        </span>
                        <span class=hdr onClick="loadothergroup('<?php echo Sanitize::encodeStringForJavascript($grp['id']); ?>')">
                            <span id="ng<?php echo Sanitize::encodeStringForDisplay($grp['id']); ?>" ><?php echo Sanitize::encodeStringForDisplay($grp['name']); ?></span>
                        </span>
                        <ul class=hide id="g<?php echo Sanitize::encodeStringForDisplay($grp['id']); ?>">
                            <li>Loading...</li>
                        </ul>
                    </li>
        <?php
    }
} else {
    echo '<li>'. _('No other users').'</li>';
}
?>
