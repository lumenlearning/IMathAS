<?php
// if login page, don't show header or header content
// if (isset($userid)) {

	if ($coursetheme=='otbsreader.css') {
		$nologo = true;
	}
	if (!isset($flexwidth) && $coursetheme == 'lumen_fw.css') {
		$smallheaderlogo = '<img src="'.$imasroot.'/img/collapse.gif"/>';
	?>
	<div id="headercontent">
	<?php
	$usernameinheader = true;
	if (isset($userid) && $selfhasuserimg==1) {
		if ($myrights > 5) {
			echo "<a href=\"#\" onclick=\"GB_show('Account Settings','$imasroot/forms.php?action=chguserinfo&greybox=true',800,'auto')\" title=\"Account Settings\">";
		}
		if(isset($GLOBALS['CFG']['GEN']['AWSforcoursefiles']) && $GLOBALS['CFG']['GEN']['AWSforcoursefiles'] == true) {
			echo "<img src=\"{$urlmode}s3.amazonaws.com/{$GLOBALS['AWSbucket']}/cfiles/userimg_sm{$userid}.jpg\" style=\"float:right;margin-top:15px;\"/>";
		} else {
			$curdir = rtrim(dirname(__FILE__), '/\\');
			$galleryPath = "$curdir/course/files/";
			echo "<img src=\"$imasroot/course/files/userimg_sm{$userid}.jpg\" style=\"float:right;margin-top:15px;\"/>";
		}
		if ($myrights > 5) {
			echo '</a>';
		}
	}
	?>

	<div id="headerbarlogo">
		<a href="<?php echo $imasroot;?>/index.php">
			<img alt="Lumen OHM, Online Learning Manager, logo" title="Click to return home" src="<?php echo $imasroot;?>/ohm/ohm-logo.png" height="60" />
		</a>
	</div>
	<div id="headerrightlinksgroup">
		<?php
			if (isset($userid)) {
				if ($myrights > 5) {
					echo "<span id=\"myname\" class=\"header-menu-item\">$userfullname</span>";
					echo "<div id=\"headerrightlinks\"><a href=\"#\" onclick=\"GB_show('Account Settings','$imasroot/forms.php?action=chguserinfo&greybox=true',800,'auto')\" title=\"Account Settings\"><span class=\"header-menu-item\">User Settings</span></a>";
					echo '<a href="#" class="header-menu-item" onclick="jQuery(\'#homemenu\').css({\'left\': jQuery(this).offset().left+\'px\', \'background-color\':\'#1E74D1\', \'color\':\'#fff\'});mopen(\'homemenu\',0)" onmouseout="mclosetime()">My Classes <img src="'.$imasroot.'/img/smdownarrow.png" style="vertical-align:middle"/></a> ';
					echo '<div id="homemenu" class="ddmenu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()"></div>';
				}
				echo "<a href=\"$imasroot/actions.php?action=logout\" class=\"header-menu-item\">Log Out</a></div>";
				// if user has more privileges than student...
				if ($myrights > 10) {
					echo "<a id=\"help-modal-open\" href=\"#\"><span id=\"help-button\"><img src=\"$imasroot/ohm/help-icon.png\" id=\"help-icon\" /><span>Help</span></span></a>";
				}
			}
		?>
	</div>

	</div>

	<script type="text/javascript" src="<?php echo $imasroot; ?>/ohm/js/helpModal.js"></script>
	<script type="text/javascript">
		$(document).ready(function(data) {
			$.get("<?php echo $imasroot; ?>/ohm/help_modal.html", function(data) {
				$('a#help-modal-open').click(function(e) {
					modal.open({
						content: data,
						height: "600",
						width: "1000"
					});
					e.preventDefault();
				});
			});
		});
	</script>

	<?php
	$nologo = true;
	$haslogout = true;
	} else if (isset($CFG['GEN']['hidedefindexmenu'])) {
		unset($CFG['GEN']['hidedefindexmenu']);
	}

// }
?>
