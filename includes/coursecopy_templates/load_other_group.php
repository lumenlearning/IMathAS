<?php
/**
 * Template for loading a specific other group's courses
 * This template handles the loadothergroup GET parameter
 */

// Ensure this file is included, not accessed directly
if (!defined('INCLUDED_FROM_COURSECOPY')) {
    exit;
}

if ($courseGroupResults->rowCount()>0) {
    $lastteacher = 0;
    $grptemplatelist = array(); //writeOtherGrpTemplates($grptemplatelist);
    while ($line = $courseGroupResults->fetch(PDO::FETCH_ASSOC)) {
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
                    <span id="n<?php echo Sanitize::encodeStringForDisplay($line['userid']); ?>" class="pii-full-name"><?php echo Sanitize::encodeStringForDisplay($line['LastName']) . ", " . Sanitize::encodeStringForDisplay($line['FirstName']) . "\n" ?>
                    </span>
                </span>
                <a class="pii-email" href="mailto:<?php echo Sanitize::emailAddress($line['email']); ?>"><span class="pii-safe">Email</span></a>
                <ul class=hide id="<?php echo Sanitize::encodeStringForDisplay($line['userid']); ?>">
<?php
            $lastteacher = $line['userid'];
        }
?>
                    <li>
                        <span class=dd>-</span>
                        <?php
                        //do class for has terms.  Attach data-termsurl attribute.
                        writeCourseInfo($line);
                        if (($line['istemplate']&2)==2) {
                            $grptemplatelist[] = $line;
                        }
                        ?>
                    </li>
<?php
    }
?>

                </ul>
            </li>
            <?php writeOtherGrpTemplates($grptemplatelist);?>

<?php
} else {
    echo '<li>'._('No group members with courses').'</li>';
}
?>
