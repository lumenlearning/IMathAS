<?php
/**
 * Template for "Template Courses" section in course copy list
 * This template displays system and group template courses
 */

// Ensure this file is included, not accessed directly
if (!defined('INCLUDED_FROM_COURSECOPY')) {
    exit;
}

//template courses
if ($courseTemplateResults->rowCount()>0 && !isset($CFG['coursebrowser'])) {
?>
<li class=lihdr>
    <span class=dd>-</span>
    <span class=hdr onClick="toggle('template')">
        <span class=btn id="btemplate">+</span>
    </span>
    <span class=hdr onClick="toggle('template')">
        <span id="ntemplate" >Template Courses</span>
    </span>
    <ul class=hide id="template">

<?php
    while ($line = $courseTemplateResults->fetch(PDO::FETCH_ASSOC)) {
?>
        <li>
            <span class=dd>-</span>
            <?php
            writeCourseInfo($line);
            ?>
        </li>

<?php
    }
    echo "            </ul>\n        </li>\n";
}
if ($groupTemplateResults->rowCount()>0 && !isset($CFG['coursebrowser'])) {
?>
<li class=lihdr>
    <span class=dd>-</span>
    <span class=hdr onClick="toggle('gtemplate')">
        <span class=btn id="bgtemplate">+</span>
    </span>
    <span class=hdr onClick="toggle('gtemplate')">
        <span id="ngtemplate" ><?php echo _('Group Template Courses'); ?></span>
    </span>
    <ul class=hide id="gtemplate">

<?php
    while ($line = $groupTemplateResults->fetch(PDO::FETCH_ASSOC)) {
?>
        <li>
            <span class=dd>-</span>
            <?php
            writeCourseInfo($line, 1);
            ?>
        </li>

<?php
    }
    echo "            </ul>\n        </li>\n";
}
?>
