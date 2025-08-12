<?php
//IMathAS:  First step of new course creation
//(c) 2018 David Lippman

/*** master php includes *******/
require_once "../init.php";

if ($myrights < 40) {
	echo "You don't have authorization to access this page";
	exit;
}

$placeinhead = '<script src="'.$staticroot.'/javascript/copyitemslist.js" type="text/javascript"></script>';
$placeinhead .= '<link rel="stylesheet" href="'.$staticroot.'/course/libtree.css" type="text/css" />';
$placeinhead .= '<script type="text/javascript" src="'.$staticroot.'/javascript/libtree.js"></script>';
require_once "../header.php";
?>

<div class="breadcrumb"><?php echo $breadcrumbbase; ?> <?php echo _('Add New Course'); ?></div>
<div class="pagetitle">
	<h1><?php echo _('Quick Start'); ?></h1>
</div>

<form method="POST" action="forms.php?from=home&action=addcourse">
	<?php
	$dispgroup = '';
	if (($myrights >= 75 || ($myspecialrights&32)==32) && isset($_GET['for']) && $_GET['for']>0 && $_GET['for'] != $userid) {
		$stm = $DBH->prepare("SELECT FirstName,LastName,groupid FROM imas_users WHERE id=?");
		$stm->execute(array($_GET['for']));
		$forinfo = $stm->fetch(PDO::FETCH_ASSOC);
		if ($myrights==100 || ($myspecialrights&32)==32 || $forinfo['groupid']==$groupid) {
			?>
			<p><?php echo _('Adding Course For'); ?>: <span class="pii-full-name">
				<?php echo Sanitize::encodeStringforDisplay($forinfo['LastName'].', '.$forinfo['FirstName']); ?>
			</span>
			<input type="hidden" name="for" value="<?php echo Sanitize::onlyInt($_GET['for']); ?>" />
			</p>
			<?php
			$dispgroup = $forinfo['groupid'];
		}
	}
	?>

	<?php if (isset($CFG['coursebrowser'])): ?>
		<!-- Copy a template course button -->
		<div id="template-course-container">
			<h2>Start with a fully-built Lumen OHM template course you can easily customize to meet the needs of your students.</h2>
			<p>Lumen template courses are designed with evidence-based teaching practices, scaffolded for students to build a strong foundation in mathematics.</p>
			
			<button id="qa-button-copy-template" type="button" onclick="showCourseBrowser(<?php echo Sanitize::encodeStringForDisplay($dispgroup); ?>)">
				<?php echo _('Use a Lumen Template'); ?>
			</button>
			<input type="hidden" name="coursebrowserctc" id="coursebrowserctc" />
		</div>
	<?php endif; ?>

	<!-- Copy a Course -->
	<div>
		<h3>Copy a Course</h3>
		<div>
			Copy MY Coruse from a previous term <span class="open-close-caret"><svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 0H0L4.81481 5L10 0Z" fill="black"/></svg></span>
		</div>
		<div>
			Copy someone else's course <span class="open-close-caret"><svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 0H0L4.81481 5L10 0Z" fill="black"/></svg></span>
		</div>
	</div>

	<!-- Advanced options -->
	<div>
		<h3 class="advanced-options-title">Advanced Options <span class="open-close-caret"><svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 0H0L4.81481 5L10 0Z" fill="black"/></svg></span></h3>
		
		<div class="advanced-options-container" id="advanced-options-expanded-container">
			<h4>Community Templates</h4>
			<p>These are courses shared by faculty members. They are not supported by Lumen and should only be used at your own risk.</p>
			
			<!-- Copy from an existing course button -->
			<p>
				<button id="qa-button-copyfrom-existing-course" type="button" onclick="showCopyOpts()">
					<?php if (isset($CFG['addcourse']['copybutton'])): ?>
						<?php echo $CFG['addcourse']['copybutton']; ?>
					<?php elseif (isset($CFG['coursebrowser'])): ?>
						<?php echo _('Copy from an existing course'); ?>
					<?php else: ?>
						<?php echo _('Copy from an existing course or template'); ?>
					<?php endif; ?>
				</button>
			</p>
			
			<div id="copyoptions" style="display:none; padding-left: 20px">
				<p><?php echo _('Select a course to copy'); ?></p>
				<?php
				$skipthiscourse = true;
				$cid = 0;
				require_once "../includes/coursecopylist.php";
				?>
			</div>
			
			<?php writeEkeyField(); ?>
			<button type="submit" id="continuebutton" disabled style="display:none"><?php echo _('Continue'); ?></button>
		</div>

		<div>
			<h4>Start From Scratch</h4>
			<p>Create your own course structure and content.</p>
			<a href="<?php echo $CFG['wwwroot']; ?>/admin/forms.php?from=home&action=addcourse">
				<?php if (isset($CFG['addcourse']['blankbutton'])): ?>
					<?php echo $CFG['addcourse']['blankbutton']; ?>
				<?php else: ?>
					<?php echo _('Start with a blank course'); ?>
				<?php endif; ?>
			</a>
		</div>
	</div>
</form>





