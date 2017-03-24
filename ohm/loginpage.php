<?php
if (!isset($imasroot)) { //don't allow direct access to loginpage.php
	header("Location: index.php");
	exit;
}
//any extra CSS, javascript, etc needed for login page
	$placeinhead = "<link rel=\"stylesheet\" href=\"$imasroot/ohm/login.css\" type=\"text/css\" />\n";
	$placeinhead .= "<link rel=\"stylesheet\" href=\"$imasroot/ohm/forms.css\" type=\"text/css\" />\n";
	$placeinhead .= "<script type=\"text/javascript\" src=\"$imasroot/javascript/jstz_min.js\" ></script>";

	$nologo = true;
	$curdir = rtrim(dirname(__FILE__), '/\\');
	require($curdir . "/../header.php");
	if (isset($_SERVER['QUERY_STRING'])) {
		 $querys = '?'.$_SERVER['QUERY_STRING'];
	 } else {
		 $querys = '';
	 }
	 if (!empty($_SESSION['challenge'])) {
		 $challenge = $_SESSION['challenge'];
	 } else {
		 //use of microtime guarantees no challenge used twice
		 $challenge = base64_encode(microtime() . rand(0,9999));
		 $_SESSION['challenge'] = $challenge;
	 }

	 $pref = 0;
	 if (isset($_COOKIE['mathgraphprefs'])) {
		 $prefparts = explode('-',$_COOKIE['mathgraphprefs']);
		 if ($prefparts[0]==2 && $prefparts[1]==2) { //img all
			$pref = 3;
		 } else if ($prefparts[0]==2) { //img math
			 $pref = 4;
		 } else if ($prefparts[1]==2) { //img graph
			 $pref = 2;
		 }

	 }
?>

<div class="login-wrapper">
	<div id="loginbox">
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'].$querys;?>">
			<?php
				if ($haslogin) {
					if ($badsession) {
						echo '<p class="error-msg">Unable to establish a session. Please check that your browser is set to allow session cookies.</p>';
					} else {
						echo "<p class=\"error-msg\">Oops! Wrong username or password.</p>\n";
					}
				}
				if ($err!='') {
					echo $err;
				}
			?>
			<!-- <h2>Login to Lumen Ohm</h2> -->
		<img id="login-logo" src="<?php echo $imasroot;?>/ohm/img/ohm-logo-800.png" />
		<div class="login-group">
			<input type="text" size="15" id="username" name="username" placeholder="Username" aria-label="Username" />
			<input type="password" size="15" id="password" name="password" placeholder="Password"  aria-label="Password" />
			<button id="login-button" type="submit">Login</button>
		</div>
		<div id="settings">JavaScript is not enabled.  JavaScript is required for <?php echo $installname; ?>.  Please enable JavaScript and reload this page</div>


		<!-- <div><a href="<?php// echo $imasroot; ?>/ohm/forms.php?action=resetpw">Forgot Password</a><br/>
		<a href="<?php// echo $imasroot; ?>/ohm/forms.php?action=lookupusername">Forgot Username</a></div>		<input type="hidden" id="tzoffset" name="tzoffset" value=""> -->
		<input type="hidden" id="tzoffset" name="tzoffset" value="">
		<input type="hidden" id="tzname" name="tzname" value="">
		<input type="hidden" id="challenge" name="challenge" value="<?php echo $challenge; ?>" />
		<script type="text/javascript">
		        var thedate = new Date();
		        document.getElementById("tzoffset").value = thedate.getTimezoneOffset();
		        var tz = jstz.determine();
		        document.getElementById("tzname").value = tz.name();
		</script>

		<div class="supplement-text">
		  <a target="_blank" href="http://lumenlearning.com/courseware-myopenmath/">What is Lumen Ohm?</a></br>
			<a href="<?php echo $imasroot; ?>/ohm/forms.php?action=resetpw">Forgot Password</a><br/>
			<a href="<?php echo $imasroot; ?>/ohm/forms.php?action=lookupusername">Forgot Username</a></br>
			<a href="<?php echo $imasroot; ?>/ohm/forms.php?action=newuser">Enroll in Your Course</a></br>
			<a href="<?php echo $imasroot; ?>/ohm/newinstructor.php?">Request an instructor account</a>
		</div>

		<script type="text/javascript">
		        function updateloginarea() {
				setnode = document.getElementById("settings");
				var html = "";
				html += '<p>Accessibility: ';
				html += "<a href='#' onClick=\"window.open('<?php echo $imasroot;?>/help.php?section=loggingin','help','top=0,width=400,height=500,scrollbars=1,left='+(screen.width-420))\">Help</a></p>";
				html += '<div id="access-select-wrapper"><select id="access-select" name="access"><option value="0">Use defaults</option>';
				html += '<option value="3">Force image-based display</option>';
				html += '<option value="6">Use KaTeX display (experimental)</option>';
				html += '<option value="1">Use text-based display</option></select></div>';

				if (!MathJaxCompatible) {
					html += '<input type=hidden name="mathdisp" value="0">';
				} else {
					html += '<input type=hidden name="mathdisp" value="1">';
				}
				if (ASnoSVG) {
					html += '<input type=hidden name="graphdisp" value="2">';
				} else {
					html += '<input type=hidden name="graphdisp" value="1">';
				}
				if (MathJaxCompatible && !ASnoSVG) {
					html += '<input type=hidden name="isok" value=1>';
				}
				setnode.innerHTML = html;
				document.getElementById("username").focus();
			}
			var existingonload = window.onload;
			if (existingonload) {
				window.onload = function() {existingonload(); updateloginarea();}
			} else {
				window.onload = updateloginarea;
			}
		</script>
		</form>
	</div>
	<footer class="footer">
		<p><?php echo $installname;?> is powered by <a href="http://www.imathas.com">IMathAS</a> &copy; 2006-2017 David Lippman |
		<a href="<?php echo $imasroot;?>/ohm/privacy.php">Privacy Policy</a></p>
	</footer>
</div>
