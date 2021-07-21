<?php if (!isset($imasroot)) {
	// This file reuses and simplifies components from
	// header.php and ohm/headercontent.php
	exit;
} ?>
<!DOCTYPE html>
<?php if (isset($CFG['locale'])) {
	echo '<html lang="' . $CFG['locale'] . '">';
} else {
	echo '<html lang="en">';
}
?>

<head>
	<title>
		<?php echo $installname;
		if (isset($pagetitle)) {
			echo " - $pagetitle";
		} ?>
	</title>
	<meta http-equiv="X-UA-Compatible" content="IE=7, IE=Edge" />
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php
	if (!empty($CFG['GEN']['uselocaljs'])) {
		echo '<script src="' . $staticroot . '/javascript/jquery.min.js"></script>';
	} else {
		echo '<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>';
		echo '<script>window.jQuery || document.write(\'<script src="' . $staticroot . '/javascript/jquery.min.js"><\/script>\')</script>';
	}
	if (empty($_SESSION['tzoffset']) && !empty($CFG['static_server'])) {
		echo '<script src="' . $CFG['static_server'] . '/javascript/staticcheck.js"></script>';
	}
	?>
	<link rel="stylesheet" href="<?php echo $staticroot . "/imascore.css?ver=020721"; ?>" type="text/css" />
	<?php
	if (isset($coursetheme)) {
		if (isset($flexwidth) || isset($usefullwidth)) {
			$coursetheme = str_replace(array('_fw1920', '_fw1000', '_fw'), '', $coursetheme);
		}
		$isfw = false;
		if (strpos($coursetheme, '_fw1920') !== false) {
			$isfw = 1920;
			$coursetheme = str_replace('_fw1920', '', $coursetheme);
		} else if (strpos($coursetheme, '_fw') !== false) {
			$isfw = 1000;
			$coursetheme = str_replace(array('_fw1000', '_fw'), '', $coursetheme);
		}
	}
	if (isset($CFG['GEN']['favicon'])) {
		echo '<link rel="shortcut icon" href="' . $CFG['GEN']['favicon'] . '" />';
	} else {
		echo '<link rel="shortcut icon" href="/favicon.ico" />';
	}
	if (isset($CFG['GEN']['appleicon'])) {
		echo '<link rel="apple-touch-icon" href="' . $CFG['GEN']['appleicon'] . '" />';
	}
	if (!empty($CFG['use_csrfp']) && class_exists('csrfProtector')) {
		echo csrfProtector::output_header_code();
	}
	?>
	<style type="text/css" media="print">
		div.breadcrumb {
			display: none;
		}

		#headerlogo {
			display: none;
		}
	</style>
	<script type="text/javascript">
		var imasroot = '<?php echo $imasroot; ?>';
		var cid = <?php echo (isset($cid) && is_numeric($cid)) ? $cid : 0; ?>;
		var staticroot = '<?php echo $staticroot; ?>';
	</script>
	<script type="text/javascript" src="<?php echo $staticroot; ?>/javascript/general.js?v=032821"></script>
	<?php
	//$_SESSION['mathdisp'] = 3;
	if (isset($CFG['locale'])) {
		$lang = substr($CFG['locale'], 0, 2);
		if (file_exists(rtrim(dirname(__FILE__), '/\\') . '/i18n/locale/' . $lang . '/messages.js')) {
			echo '<script type="text/javascript" src="' . $staticroot . '/i18n/locale/' . $lang . '/messages.js"></script>';
		}
	}
	if (isset($placeinhead)) {
		echo $placeinhead;
	}
	$curdir = rtrim(dirname(__FILE__), '/\\');
	if (isset($CFG['GEN']['headerscriptinclude'])) {
		require("$curdir/{$CFG['GEN']['headerscriptinclude']}");
	}
	if (isset($coursetheme)) {
		echo '<link rel="stylesheet" href="' . $staticroot . "/themes/$coursetheme?v=042217\" type=\"text/css\" />";
	}
	echo '<link rel="stylesheet" href="' . $staticroot . '/handheld.css?v=071320" media="only screen and (max-width:480px)"/>';
	echo '<link rel="stylesheet" href="https://lux.lumenlearning.com/use-lux/1.0.3/lux-components.min.css" />';
	echo '<link rel="stylesheet" href="'.$imasroot.'/ohm/css/eula.css" />';

	if (isset($CFG['GEN']['translatewidgetID'])) {
		echo '<meta name="google-translate-customization" content="' . $CFG['GEN']['translatewidgetID'] . '"></meta>';
	}
	if (isset($_SESSION['ltiitemtype'])) {
		echo '<script type="text/javascript">
						jQuery(sendLTIresizemsg);
					</script>';
	}
	echo "</head>\n";
	if ($isfw !== false) {
		echo "<body class=\"fw$isfw\">\n";
	} else {
		echo "<body class=\"notfw\">\n";
	}

	echo '<div class="mainbody">';

	$insertinheaderwrapper = ' '; //"<h1>$coursename</h1>";
	if ('true' != $_GET['lms']) {

		if (!isset($flexwidth) && !isset($hideAllHeaderNav)) {
			echo '<div class="headerwrapper">';
		}
		if ($coursetheme == 'otbsreader.css') {
			$nologo = true;
		}
		if (!isset($flexwidth) && $coursetheme == 'lumen.css') {
			$smallheaderlogo = '<img src="' . $imasroot . '/img/collapse.gif"/>';
		?>

			<div id="headercontent" style='font-size: 14px; font-family: "Open Sans", "Trebuchet MS", "Arial", "Helvetica", "sans-serif"'>

				<div class="headerbar-wrapper">
					<div id="headerbarlogo">
						<a href="<?php echo $imasroot; ?>/index.php">
							<img alt="Lumen OHM, Online Learning Manager, logo" title="Click to return home" src="<?php echo $imasroot; ?>/ohm/img/ohm-logo-800.png" height="60" />
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
							echo "<span id=\"myname\" class=\"header-menu-item\">" .
								Sanitize::encodeStringForDisplay($userfullname)
								. "<img id=\"avatar\" alt=\"\" src=\"$imasroot/ohm/img/blank-avatar.png\" /></span>";
						}
						echo "<div id=\"headerrightlinks\"><a href=\"$imasroot/actions.php?action=logout\" class=\"header-menu-item\">Log Out</a></div>";
					}
					?>
				</div>
			</div>

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
			$nologo = true;
			$haslogout = true;
		} else if (isset($CFG['GEN']['hidedefindexmenu'])) {
			unset($CFG['GEN']['hidedefindexmenu']);
		}

		$didnavlist = false;
		$essentialsnavcnt = 0;
		if (!isset($flexwidth) && !isset($hideAllHeaderNav)) {
			echo '</div>';
		}
	}
	echo '<div class="midwrapper" role="main">';

	//CUSTOMIZE:  put a small (max 120px wide) logo on upper right of course pages

	if (!isset($nologo)) {
		echo '<div id="headerlogo" class="hideinmobile" ';
		if ($myrights > 10 && !$ispublic && !isset($_SESSION['ltiitemtype'])) {
			echo 'onclick="mopen(\'homemenu\',';
			if (isset($cid) && is_numeric($cid)) {
				echo $cid;
			} else {
				echo 0;
			}
			echo ')" onmouseout="mclosetime()"';
		}
		echo '>' . $smallheaderlogo . '</div>';
		if ($myrights > 10 && !$ispublic && !isset($_SESSION['ltiitemtype'])) {
			echo '<div id="homemenu" class="ddmenu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
			echo '</div>';
		}
	}


	?>
