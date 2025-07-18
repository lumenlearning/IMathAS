<?php
//IMathAS: Common Catridge v1.1 Export
//(c) 2011 David Lippman

require_once "../init.php";
require_once "../includes/copyiteminc.php";
require_once "../includes/loaditemshowdata.php";

if (!is_numeric($_GET['cid'])) {
    echo 'Invalid course ID.';
    exit;
}

$cid = Sanitize::courseId($_GET['cid']);
if (!isset($teacherid)) {
    echo 'You must be a teacher to access this page';
    exit;
}

$path = realpath("../course/files");

if (isset($_GET['create']) && isset($_POST['whichitems'])) {
    if ($_POST['lms'] == 'bb' && $_POST['carttype'] == 'bb') {
        require_once "bbexport-generate.php";
    } else {
        require_once "ccexport-generate.php";
    }
    exit;
} else {
    $stm = $DBH->prepare("SELECT itemorder,dates_by_lti,ltisecret FROM imas_courses WHERE id=:id");
    $stm->execute(array(':id' => $cid));
    list($items, $datesbylti, $ltisecret) = $stm->fetch(PDO::FETCH_NUM);
    $items = unserialize($items);

    $ids = array();
    $types = array();
    $names = array();
    $sums = array();
    $parents = array();
    $agbcats = array();
    $prespace = array();
    $itemshowdata = loadItemShowData($items, false, true, false, false, false, true);
    getsubinfo($items, '0', '', false, '|- ');

    $stm = $DBH->prepare("SELECT id FROM imas_users WHERE (rights=11 OR rights=76 OR rights=77) AND groupid=?");
    $stm->execute(array($groupid));
    $hasGroupLTI = ($stm->fetchColumn() !== false);
    // look for LTI 1.3 connection
    $stm = $DBH->prepare("SELECT deploymentid FROM imas_lti_groupassoc WHERE groupid=?");
    $stm->execute(array($groupid));
    $hasLTI13 = ($stm->fetchColumn() !== false);
    $hasGroupLTI = $hasGroupLTI || $hasLTI13;
    if ($hasGroupLTI && !empty($CFG['LTI']['noCourseLevel']) && $myrights < 100) {
        $groupLTInote = '<p>Your school already has a school-wide LTI key and secret established.  You do not need to set up a course-level configuration.</p>';
    } else if (!empty($CFG['LTI']['noCourseLevel']) && !empty($CFG['LTI']['noGlobalMsg'])) {
        $groupLTInote = '<p>' . $CFG['LTI']['noGlobalMsg'] . '</p>';
    } else {
        if ($hasGroupLTI) {
            $groupLTInote = '<p>It looks like your school may already have a school-wide LTI key and secret established - check with your LMS admin. ';
            $groupLTInote .= 'If so, you will not need to set up a course-level configuration.<br/> ';
            $groupLTInote .= 'If you do need to set up a course-level configuration, <a href="#" onclick="$(\'ul.ltikeyinfo\').slideDown();return false;">show course level key/secret</a></p>';
            $groupLTInote .= '<ul class="ltikeyinfo" style="display:none;">';
        } else {
            $groupLTInote = '<p>Your school does not appear to have a school-wide LTI key and secret established. ';
            $groupLTInote .= 'To set up a course-level configuration, you will need this information:</p>';
            $groupLTInote .= '<ul class="ltikeyinfo">';
        }
        $groupLTInote .= '<li>Key: LTIkey_' . $cid . '_1</li>';
        $groupLTInote .= '<li>Secret: ';
        if ($ltisecret == '') {
            $groupLTInote .= 'You have not yet set up an LTI secret for your course.  To do so, visit the ';
            $groupLTInote .= '<a href="forms.php?action=modify&id=' . $cid . '&cid=' . $cid . '">Course Settings</a> page.';
        } else {
            $groupLTInote .= Sanitize::encodeStringForDisplay($ltisecret);
        }
        $groupLTInote .= '</li></ul>';
    }

    $pagetitle = "CC Export";

    $placeinhead = '<script type="text/javascript">
	 function updatewhichsel(el) {
	   if (el.value=="select") { $("#itemselectwrap").show();}
	   else {$("#itemselectwrap").hide()};
	 }
	 function updatelms(el) {
	   $(".lmsblock").hide();
	   $("#lms"+el.value).show();
	 }
	 function chkgrp(frm, arr, mark) {
	  var els = frm.getElementsByTagName("input");
	  for (var i = 0; i < els.length; i++) {
		  var el = els[i];
		  if (el.type=="checkbox" && (el.id.indexOf(arr+".")==0 || el.id.indexOf(arr+"-")==0 || el.id==arr)) {
	     	       el.checked = mark;
		  }
	  }
	}
	 </script>';
    $placeinhead .= '<style type="text/css">
	 .nomark.canvasoptlist li { text-indent: -25px; margin-left: 25px;}
	 </style>';
    require_once "../header.php";
    echo "<div class=breadcrumb>$breadcrumbbase ";
    if (empty($_COOKIE['fromltimenu'])) {
        echo " <a href=\"../course/course.php?cid=$cid\">" . Sanitize::encodeStringForDisplay($coursename) . "</a> &gt; ";
    }
    echo _('Export For Another LMS'), '</div>';
    if (empty($_COOKIE['fromltimenu'])) {
        echo '<div class="cpmid">';
        if (!isset($CFG['GEN']['noimathasexportfornonadmins']) || $myrights >= 75) {
            echo '<a href="exportitems2.php?cid=' . $cid . '">Export for another IMathAS system or as a backup for this system</a> ';
        }
        if ($myrights == 100) {
            echo '| <a href="jsonexport.php?cid=' . $cid . '" name="button">Export OEA JSON</a>';
        }
        echo '</div>';
    }

    echo '<h2>Export For Another LMS</h2>';
    echo '<p>This feature will allow you to export package which can then be loaded into another Learning Management System. ';
    echo 'Inline text, web links, course files, and forums will all transfer reasonably well.</p>';
    echo '<p>Since LMSs cannot support the type of question types that this system ';
    echo 'does, assessments are exported as LTI (learning tools interoperability) placements back to this system.  Not all LMSs ';
    echo 'support this standard yet, so your assessments may not transfer.</p>';

    if ($enablebasiclti == false) {
        echo '<p class="noticetext">Note: Your system does not currently have LTI enabled.  Contact your system administrator</p>';
    }
    echo '<form id="qform" method="post" action="ccexport.php?cid=' . $cid . '&create=true" class="nolimit">';
    ?>
	<p><label>Items to export: <select name="whichitems" onchange="updatewhichsel(this)">
		<option value="all" selected>Export entire course</option>
		<option value="select">Select individual items to export</option>
		</select></label>
	</p>
	<div id="itemselectwrap" style="display:none;">

	Check: <a href="#" onclick="return chkAllNone('qform','checked[]',true)">All</a> <a href="#" onclick="return chkAllNone('qform','checked[]',false)">None</a>

	<table cellpadding=5 class=gb>
    <caption class="sr-only">Items</caption>
	<thead>
		<tr><th></th><th>Title</th></tr>
	</thead>
	<tbody>
<?php
$alt = 0;
    for ($i = 0; $i < (count($ids)); $i++) {
        if ($alt == 0) {echo "<tr class=even>";
            $alt = 1;} else {echo "<tr class=odd>";
            $alt = 0;}
        echo '<td>';
        if (strpos($types[$i], 'Block') !== false) {
            $thisid = $ids[$i];
            echo '<input type=checkbox name="checked[]" id="' . Sanitize::encodeStringForDisplay($thisid) . '" ';
            echo 'onClick="chkgrp(this.form, \'' . Sanitize::encodeStringForDisplay($ids[$i]) . '\', this.checked);" ';
            echo 'value="' . Sanitize::encodeStringForDisplay($ids[$i]) . '">';
        } else {
            $thisid = $parents[$i] . '.' . $ids[$i];
            echo '<input type=checkbox name="checked[]" id="' . Sanitize::encodeStringForDisplay($thisid) . '" ';
            echo 'value="' . Sanitize::encodeStringForDisplay($ids[$i]) . '">';
        }
        echo '</td>';
        $tdpad = 5 * strlen($prespace[$i]);

        echo '<td style="padding-left:' . $tdpad . 'px"><img alt="' . $types[$i] . '" title="' . $types[$i] . '" src="' . $staticroot . '/img/';
        switch ($types[$i]) {
            case 'Calendar':echo $CFG['CPS']['miniicons']['calendar'];
                break;
            case 'InlineText':echo $CFG['CPS']['miniicons']['inline'];
                break;
            case 'LinkedText':echo $CFG['CPS']['miniicons']['linked'];
                break;
            case 'Forum':echo $CFG['CPS']['miniicons']['forum'];
                break;
            case 'Wiki':echo $CFG['CPS']['miniicons']['wiki'];
                break;
            case 'Block':echo $CFG['CPS']['miniicons']['folder'];
                break;
            case 'Assessment':echo $CFG['CPS']['miniicons']['assess'];
                break;
            case 'Drill':echo $CFG['CPS']['miniicons']['drill'];
                break;
        }
        echo '" class="floatleft"/><div style="margin-left:21px"><label for="' . Sanitize::encodeStringForDisplay($thisid) . '">' . Sanitize::encodeStringForDisplay($names[$i]) . '</label></div></td>';

        echo '</tr>';
    }
    ?>
		</tbody>
		</table>
	</div>
	<p><label>Your LMS: <select name="lms" onchange="updatelms(this)">
		<option value="canvas" selected>Canvas</option>
		<option value="bb">BlackBoard</option>
		<option value="d2l">D2L Brightspace</option>
		<option value="moodle">Moodle</option>
		<option value="other">Other</option>
		</select></label>
	</p>
	<div id="lmscanvas" class="lmsblock">
		<fieldset>
		<legend>Canvas Export Options</legend>
		<ul class="nomark canvasoptlist">
		<li><label><input type=checkbox name=includeappconfig value=1 <?php if (!$hasGroupLTI) {echo 'checked';}?> /> 
            Include App Config?</label> Do not include it if you have site-wide credentials,
			or if you are doing a second import into a course that already has a configuration.</li>
		<li><label><input type=checkbox name=includegbcats value=1 checked /> 
            Include <?php echo $installname; ?> gradebook setup and categories</label></li>
		<li><label><input type=checkbox name=includeduedates value=1 checked /> 
            Include <?php echo $installname; ?> due dates for assessments</label></li>
		<li><label><input type=checkbox name=includestartdates value=1 /> 
            Include <?php echo $installname; ?> start dates for assessments and blocks</label><br/>
			<span class="small">Blocks will only include the start date if they are set to hide contents from students when not available</span></li>
		<li><label><input type=checkbox name=newtab value=1 /> 
            Open <?php echo $installname; ?> assignments in new window/tab</label></li>
        <li><label><input type=checkbox name=datesbylti value=1 <?php if ($datesbylti > 0) {
        echo 'checked';
    }
    ?> /> Allow Canvas to set <?php echo $installname; ?> due dates</label><br/>
			<span class="small">This option can also be set on the Course Settings page.</span></li>
		</ul>
		</fieldset>
		<p><button type="submit">Download Export Cartridge</button></p>
		<p><a href="../help.php?section=lticanvas" target="_blank">Canvas Setup Instructions</a></p>
		<?php echo $groupLTInote; ?>
        <p>&nbsp;</p>
        <p>If you don't want to embed in Canvas, but want students to know about 
           <?php echo $installname; ?> assignments in the Canvas calendar, you can 
           <a href="canvascalexport.php?cid=<?php echo $cid;?>">export Canvas calendar items</a>.
           Note it's best to do this after <em>all</em> assignments for the course 
           are ready and have dates set. Otherwise, be careful on import to carefully 
           select what you import, or else you'll end up with duplicate events.</p>
	</div>
	<div id="lmsbb" style="display:none" class="lmsblock">
		<fieldset>
		<legend>BlackBoard Export Options</legend>
		<ul class="nomark bboptlist">
		  <li><label><input type=checkbox name=includeduedates value=1 checked /> Include <?php echo $installname; ?> due dates for assessments</label></li>
		</ul>
		</fieldset>
		<p><button type="submit" name="carttype" value="bb">Download BlackBoard Cartridge</button></p>
		<p><a href="../help.php?section=ltibb" target="_blank">BlackBoard Setup Instructions</a></p>
		<?php echo $groupLTInote; ?>
	</div>
	<div id="lmsmoodle" style="display:none" class="lmsblock">
		<p><button type="submit">Download Export Cartridge</button></p>
		<p><a href="../help.php?section=ltimoodle" target="_blank">Moodle Setup Instructions</a></p>
		<?php echo $groupLTInote; ?>
		<ul>
		<li>Tool Base URL: <?php echo $GLOBALS['basesiteurl'] . '/bltilaunch.php'; ?> </li>
		</ul>
	</div>
	<div id="lmsd2l" style="display:none" class="lmsblock">
		<p><button type="submit">Download Export Cartridge</button></p>
		<p><a href="../help.php?section=ltid2l" target="_blank">Brightspace Setup Instructions</a></p>
		<?php echo $groupLTInote; ?>
		<ul>
		<li>Launch Point: <?php echo $GLOBALS['basesiteurl'] . '/bltilaunch.php'; ?> </li>
		</ul>
	</div>
	<div id="lmsother" style="display:none" class="lmsblock">
		<p><button type="submit">Download Export Cartridge</button></p>
		<p><a href="../help.php?section=ltiother" target="_blank">LMS Setup Instructions</a></p>
		<?php echo $groupLTInote; ?>
		<ul>
		<li>Launch URL: <?php echo $GLOBALS['basesiteurl'] . '/bltilaunch.php'; ?> </li>
		</ul>

	</div>

	<?php

    echo '</form>';

}
require_once "../footer.php";

?>
