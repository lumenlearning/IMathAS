<?php
// IMathAS: First step of new course creation
// (c) 2018 David Lippman

/*** master php includes *******/
require_once "../init.php";

if ($myrights < 40) {
	echo "You don't have authorization to access this page";
	exit;
}

$placeinhead = '<script src="' . $staticroot . '/javascript/copyitemslist.js" type="text/javascript"></script>';
$placeinhead .= '<link rel="stylesheet" href="' . $staticroot . '/course/libtree.css" type="text/css" />';
$placeinhead .= '<script type="text/javascript" src="' . $staticroot . '/javascript/libtree.js"></script>';

require_once "../header.php";
?>

<div class="breadcrumb">
	<?php echo $breadcrumbbase; ?> <?php echo _('Add New Course'); ?>
</div>

<div class="pagetitle">
	<h1><?php echo _('Quick Start'); ?></h1>
</div>

<div id="quick-start-content-container">
	<form method="POST" action="forms.php?from=home&action=addcourse">
		<?php
		$dispgroup = '';
		
		// Check if adding course for another user
		if (($myrights >= 75 || ($myspecialrights & 32) == 32) && 
			isset($_GET['for']) && $_GET['for'] > 0 && $_GET['for'] != $userid) {
			
			$stm = $DBH->prepare("SELECT FirstName, LastName, groupid FROM imas_users WHERE id = ?");
			$stm->execute(array($_GET['for']));
			$forinfo = $stm->fetch(PDO::FETCH_ASSOC);
			
			if ($myrights == 100 || ($myspecialrights & 32) == 32 || $forinfo['groupid'] == $groupid) {
				?>
				<p>
					<?php echo _('Adding Course For'); ?>: 
					<span class="pii-full-name">
						<?php echo Sanitize::encodeStringforDisplay($forinfo['LastName'] . ', ' . $forinfo['FirstName']); ?>
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
			<div id="template-course-container" class="quick-start-wrapper">
				<h2>
					Start with a fully-built Lumen OHM template course you can easily customize 
					to meet the needs of your students.
				</h2>
				<p>
					Lumen template courses are designed with evidence-based teaching practices, 
					scaffolded for students to build a strong foundation in mathematics.
				</p>
				
				<button id="qa-button-copy-template" type="button" 
						onclick="showCourseBrowser(<?php echo Sanitize::encodeStringForDisplay($dispgroup); ?>)">
					<?php echo _('Use a Lumen Template'); ?>
				</button>
				<input type="hidden" name="coursebrowserctc" id="coursebrowserctc" />
			</div>
		<?php endif; ?>

		<!-- Copy a Course -->
		<div id="copy-course-container" class="quick-start-wrapper">
			<h3>Copy a Course</h3>
			
			<div class="collapsible-item">
				Copy MY course from a previous term 
				<span class="open-close-caret">
					<svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M10 0H0L4.81481 5L10 0Z" fill="black"/>
					</svg>
				</span>
			</div>

			<?php
			// Load data needed for "My Courses" section
			$stm = $DBH->prepare("SELECT jsondata FROM imas_users WHERE id=:id");
			$stm->execute(array(':id'=>$userid));
			$userjson = json_decode($stm->fetchColumn(0), true);

			$myCourseResult = $DBH->prepare("SELECT ic.id,ic.name,ic.termsurl,ic.copyrights FROM imas_courses AS ic,imas_teachers WHERE imas_teachers.courseid=ic.id AND imas_teachers.userid=:userid AND ic.available<4 ORDER BY ic.name");
			$myCourseResult->execute(array(':userid'=>$userid));
			$myCourses = array();
			$myCoursesDefaultOrder = array();
			while ($line = $myCourseResult->fetch(PDO::FETCH_ASSOC)) {
				$myCourses[$line['id']] = $line;
				$myCoursesDefaultOrder[] = $line['id'];
			}
			
			// Define constant and include utilities
			define('INCLUDED_FROM_COURSECOPY', true);
			require_once(__DIR__ . '/../includes/coursecopy_templates/utilities.php');
			?>
			
			<div class="copy-course-content-mine">
				<?php include_once(__DIR__ . '/../includes/coursecopy_templates/my_courses.php'); ?>
				
				<?php writeEkeyField(); ?>
				
				<button type="submit" id="continuebutton" disabled style="display:none">
					<?php echo _('Continue'); ?>
				</button>
			</div>
		

			<div class="copy-course-content-other-title collapsible-item close" onClick={copyOtherCourseToggle()}>
				Copy someone else's course 
				<span class="open-close-caret">
					<svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M10 0H0L4.81481 5L10 0Z" fill="black"/>
					</svg>
				</span>
			</div>

			<div class="copy-course-content-other hide">
				<p>
					<input type="text" size="7" id="cidlookup" />
					<button type="button" onclick="lookupcid()"><?php echo _('Look up course'); ?></button>
					<span id="cidlookupout" style="display:none;"><br/>
					<input type=radio name=ctc value=0 id=cidlookupctc />
					<span id="cidlookupname"></span>
					</span>
					<span id="cidlookuperr"></span>
				</p>
			</div>
		</div>

		<!-- Advanced options -->
		<div id="advanced-options-container" class="quick-start-wrapper close">
			<p class="advanced-options-title collapsible-item" onClick={advancedOptionsToggle()}>
				Advanced options 
				<span class="open-close-caret">
					<svg width="10" height="5" viewBox="0 0 10 5" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M10 0H0L4.81481 5L10 0Z" fill="black"/>
					</svg>
				</span>
			</p>
			
			<div id="advanced-options-container-expanded" class="hide">
				<div class="advanced-options-content-wrapper">
					<h4>Community Templates</h4>
					<p>
						These are courses shared by faculty members. They are not supported by Lumen 
						and should only be used at your own risk.
					</p>
					<a href="<?php echo $CFG['wwwroot']; ?>/admin/forms.php?from=home&action=addcourse">
						<?php echo _('Use a community template'); ?>
					</a>
				</div>

				<div class="advanced-options-content-wrapper">
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
		</div>
	</form>
</div>




