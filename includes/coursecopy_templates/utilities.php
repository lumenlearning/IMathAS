<?php
/**
 * Utility functions for course copy templates
 * This file contains common functions used across all templates
 */

// Ensure this file is included, not accessed directly
if (!defined('INCLUDED_FROM_COURSECOPY')) {
    exit;
}

/**
 * Print course order based on user preferences
 */
function printCourseOrder($order, $data, &$printed) {
    foreach ($order as $item) {
        if (is_array($item)) {
            echo '<li class="coursegroup"> ';
            echo '<b>'.Sanitize::encodeStringForDisplay($item['name']).'</b>';
            echo '<ul class="nomark">';
            printCourseOrder($item['courses'], $data, $printed);
            echo '</ul></li>';
        } else if (isset($data[$item])) {
            printCourseLine($data[$item]);
            $printed[] = $item;
        }
    }
}

/**
 * Print a single course line
 */
function printCourseLine($data) {
    echo '<li>';
    writeCourseInfo($data, -1);
    echo '</li>';
}

/**
 * Write course information with copyright handling
 */
function writeCourseInfo($line, $skipcopyright=2) {
    global $imasroot;
    $itemclasses = array();
    if ($line['copyrights']<$skipcopyright) {
        $itemclasses[] = 'copyr';
    }
    if ($line['termsurl']!='') {
        $itemclasses[] = 'termsurl';
    }
    echo '<label><input type="radio" name="ctc" value="' . Sanitize::encodeStringForDisplay($line['id']) . '" ' . ((count($itemclasses)>0)?'class="' . implode(' ',$itemclasses) . '"':'');
    if ($line['termsurl']!='') {
        echo ' data-termsurl="'.Sanitize::url($line['termsurl']).'"';
    }
    echo '>';
    echo Sanitize::encodeStringForDisplay($line['name']) . '</label>';

    if ($line['copyrights']<$skipcopyright) {
        echo "&copy;\n";
    } else {
        echo " <a href=\"$imasroot/course/course.php?cid=" . Sanitize::courseId($line['id']) . "\" target=\"_blank\" class=\"small\">"._("Preview")."</a>";
    }
}

/**
 * Write other group templates section
 */
function writeOtherGrpTemplates($grptemplatelist) {
    if (count($grptemplatelist)==0) { return;}
    $uniqid = uniqid();
    ?>
    <li class=lihdr>
    <span class=dd>-</span>
    <span class=hdr onClick="toggle('OGT<?php echo $uniqid; ?>')">
        <span class=btn id="bOGT<?php echo $uniqid; ?>">+</span>
    </span>
    <span class=hdr onClick="toggle('OGT<?php echo $uniqid; ?>')">
        <span id="nOGT<?php echo $uniqid; ?>" ><?php echo _('Group Templates') . "\n" ?>
        </span>
    </span>
    <ul class=hide id="OGT<?php echo $uniqid; ?>">
    <?php
    $showncourses = array();
    foreach ($grptemplatelist as $gt) {
        if (in_array($gt['courseid'], $showncourses)) {continue;}
        echo '<li><span class=dd>-</span>';
        writeCourseInfo($gt);
        $showncourses[] = $gt['courseid'];
        echo '<li>';
    }
    echo '</ul></li>';
}

/**
 * Write enrollment key and terms fields
 */
function writeEkeyField() {
?>
    <p id="ekeybox" style="display:none;">
    <?php echo _('For courses marked with &copy;, you must supply the course enrollment key to show permission to copy the course.'); ?><br/>
    <?php echo _('Enrollment key:'); ?> <input type=text name=ekey id=ekey size=30></p>

    <p id="termsbox" style="display:none;">
    <?php echo sprintf(_('This course has additional %sTerms of Use %s you must agree to before copying the course.'),'<a target="_blank" href="" id="termsurl">','</a>'); ?>'<br/>
    <input type="checkbox" name="termsagree" /> <?php echo _('I agree to the Terms of Use specified in the link above.'); ?></p>
<?php
}
?>
