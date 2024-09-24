<?php
// if login page, don't show header or header content
// if (isset($userid)) {

	if ($coursetheme=='otbsreader.css') {
		$nologo = true;
	}
	if (!isset($flexwidth) && $coursetheme == 'lumen.css') {
		$smallheaderlogo = '<img src="'.$imasroot.'/img/collapse.gif"/>';
	?>
	<div id="headercontent" style='font-size: 14px; font-family: "Open Sans", "Trebuchet MS", "Arial", "Helvetica", "sans-serif"'>

	<div class="headerbar-wrapper">
		<div id="headerbarlogo">
			<a href="<?php echo $imasroot;?>/index.php">
				<img alt="Lumen OHM, Online Learning Manager, logo" title="Click to return home" src="<?php echo $imasroot;?>/ohm/img/ohm-logo-800.png" height="60" />
			</a>
		</div>
		<div id="headerbar-menu-toggle">
			<img class="menu-dropdown-btn" src="<?php echo $imasroot; ?>/ohm/img/menu-dropdown-btn.png" alt="header bar menu dropdown toggle button" />
		</div>
	</div>
	<div class="linksgroup" id="headerrightlinksgroup">
		<?php
			if (isset($userid)) {
				if ($myrights > 5) {
					$usernameinheader = true;
					echo "<span id=\"myname\" class=\"header-menu-item pii-full-name\">" .
                        Sanitize::encodeStringForDisplay($userfullname)
                        . "<img id=\"avatar\" src=\"$imasroot/ohm/img/blank-avatar.png\" /></span>";
					// User avatar logic
					// if (1 == $selfhasuserimg && isset($GLOBALS['CFG']['GEN']['AWSforcoursefiles']) && $GLOBALS['CFG']['GEN']['AWSforcoursefiles'] == true) {
					// 	$curdir = rtrim(dirname(__FILE__), '/\\');
					// 	$galleryPath = "$curdir/course/files/";
					// 	echo "<img id=\"avatar\" src=\"$imasroot/course/files/userimg_sm{$userid}.jpg\" />";
					// } else {
					// 	echo "<img id=\"avatar\" src=\"$imasroot/ohm/img/blank-avatar.png\" />";
					// }
					echo "<div id=\"headerrightlinks\"><a href=\"#\" onclick=\"GB_show('Account Settings','$imasroot/forms.php?action=chguserinfo&greybox=true',800,'auto')\" title=\"Account Settings\"><span class=\"header-menu-item\">User Settings</span></a>";
					echo '<a href="#" class="header-menu-item" onclick="jQuery(\'#homemenu\').css({\'left\': jQuery(this).offset().left+\'px\', \'background-color\':\'#1E74D1\', \'color\':\'#fff\'});mopen(\'homemenu\',0)" onmouseout="mclosetime()">My Classes <img src="'.$imasroot.'/img/smdownarrow.png" style="vertical-align:middle"/></a> ';
					echo '<div id="homemenu" class="ddmenu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()"></div>';
				}
				echo "<a href=\"$imasroot/actions.php?action=logout\" class=\"header-menu-item\">Log Out</a></div>";
				// if user has more privileges than student...
				if ($myrights > 10) {
					echo "<a id=\"help-modal-open\" href=\"#\"><span id=\"help-button\"><img src=\"$imasroot/ohm/img/help-icon.png\" id=\"help-icon\" /><span>Help</span></span></a>";
				}
			}
		?>
	</div>

	</div>

	<script type="text/javascript" src="<?php echo $imasroot; ?>/ohm/js/helpModal.js"></script>

	<script type="text/javascript">
		$(document).ready(function() {
			$('img.menu-dropdown-btn').click(function(e) {
				var x = document.getElementById('headerrightlinksgroup');
				if (x.className === 'linksgroup') {
					x.className += ' responsive';
					$('#navlistcont').addClass('responsive');
				} else {
					x.className = 'linksgroup';
					$('#navlistcont').removeClass('responsive');
				}
			});
		});
	</script>

<?php
    $helpModalQueryString = empty($_REQUEST['cid']) ? '' : '?cid=' . intval($_REQUEST['cid']);
    $helpModalUrl = sprintf('%s/ohm/help_modal.php%s', $imasroot, $helpModalQueryString);
?>

	<script type="text/javascript">
		$(document).ready(function(data) {
			$.get("<?php echo $helpModalUrl; ?>", function(data) {
				$('a#help-modal-open').click(function(e) {
					modal.open({
						content: data,
						height: "auto",
						width: "80%"
					});
					e.preventDefault();
				});
			});
		});
	</script>

        <script>
          $(document).ready(function() {
            $('div#navlistcont')
              .css("font-family", '"Open Sans", "Trebuchet MS", "Arial", "Helvetica", "sans-serif"')
              .css("font-size", "14px");
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
