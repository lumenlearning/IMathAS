<?php
/**
 * Template for "My Group's Courses" section in course copy list
 * This template displays courses from other teachers in the same group
 */

// Ensure this file is included, not accessed directly
if (!defined('INCLUDED_FROM_COURSECOPY')) {
    exit;
}
?>

<li class=lihdr><span class=dd>-</span>
    <span class=hdr onClick="toggle('grp')">
        <span class=btn id="bgrp">+</span>
    </span>
    <span class=hdr onClick="toggle('grp')">
        <span id="ngrp" ><?php echo _("My Group's Courses"); ?></span>
    </span>
    <ul class=hide id="grp">

<?php
//group's courses
if ($courseTreeResult->rowCount()>0) {
    while ($line = $courseTreeResult->fetch(PDO::FETCH_ASSOC)) {
        if ($line['userid']!=$lastteacher) {
            if ($lastteacher!=0) {
                echo "                </ul>\n            </li>\n";
            }
?>
            <li class=lihdr>
                <span class=dd>-</span>
                <span class=hdr onClick="toggle(<?php echo Sanitize::encodeStringForJavascript($line['userid']); ?>)">
                    <span class=btn id="b<?php echo Sanitize::encodeStringForDisplay($line['userid']); ?>">+</span>
                </span>
                <span class=hdr onClick="toggle(<?php echo Sanitize::encodeStringForJavascript($line['userid']); ?>)">
                    <span id="n<?php echo Sanitize::encodeStringForDisplay($line['userid']); ?>"><?php echo Sanitize::encodeStringForDisplay($line['LastName']) . ", " . Sanitize::encodeStringForDisplay($line['FirstName']) . "\n" ?>
                    </span>
                </span>
                <a href="mailto:<?php echo Sanitize::emailAddress($line['email']); ?>">Email</a>
                <ul class=hide id="<?php echo Sanitize::encodeStringForDisplay($line['userid']); ?>">
<?php
            $lastteacher = $line['userid'];
        }
?>
                    <li>
                        <span class=dd>-</span>
                        <?php
                        writeCourseInfo($line, 1);
                        ?>
                    </li>
<?php
    }
    echo "                        </ul>\n                    </li>\n";
    echo "                </ul>            </li>\n";
} else {
    echo "                </ul>\n            </li>\n";
}
?>
