<?php
//any extra CSS, javascript, etc needed for login page
	$placeinhead = "<link rel=\"stylesheet\" href=\"$imasroot/ohm/login.css?v=20200520\" type=\"text/css\" />\n";
	$placeinhead .= "<link rel=\"stylesheet\" href=\"$imasroot/ohm/forms.css\" type=\"text/css\" />\n";
	$placeinhead .= "<script type=\"text/javascript\" src=\"$imasroot/javascript/jstz_min.js\" ></script>";

	$nologo = true;
	require(__DIR__ . "/../header.php");
	if (!empty($_SERVER['QUERY_STRING'])) {
		 $querys = '?'.Sanitize::fullQueryString($_SERVER['QUERY_STRING']);
	 } else {
		 $querys = '';
	 }
	 $loginFormAction = $GLOBALS['basesiteurl'] . substr($_SERVER['SCRIPT_NAME'],strlen($imasroot)) . $querys;
	 
	 if (!empty($_SESSION['challenge'])) {
		 $challenge = $_SESSION['challenge'];
	 } else {
		 //use of microtime guarantees no challenge used twice
		 $challenge = base64_encode(microtime() . rand(0,9999));
		 $_SESSION['challenge'] = $challenge;
	 }

?>

<div class="login-wrapper">
	<div id="loginbox">
		<form method="post" action="<?php echo $loginFormAction;?>">
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
				<input type="text" size="15" id="username" class="pii-username" name="username" placeholder="Username" aria-label="Username" />
				<input type="password" size="15" id="password" name="password" placeholder="Password"  aria-label="Password" />
				<button id="login-button" class="login-button" type="submit">Login</button>
			</div>
			<div><noscript>JavaScript is not enabled.  JavaScript is required for <?php echo $installname; ?>.  Please enable JavaScript and reload this page</noscript></div>


			<input type="hidden" id="tzoffset" name="tzoffset" value="">
			<input type="hidden" id="tzname" name="tzname" value="">
			<input type="hidden" id="challenge" name="challenge" value="<?php echo $challenge; ?>" />
			<script type="text/javascript">
			$(function() {
				var thedate = new Date();
				document.getElementById("tzoffset").value = thedate.getTimezoneOffset();
				var tz = jstz.determine();
				document.getElementById("tzname").value = tz.name();
				$("#username").focus();
			});
			</script>
		</form>
		<p class="or"><span>or</span></p>
		<form action="<?php echo $imasroot; ?>/ohm/enroll.php" method="get"><div class="login-group">
			<button id="enroll-button">Enroll In a New Course</button></div>
		</form>
		<div class="supplement-text">
	<a target="_blank" href="https://www.lumenlearning.com/what/ohm/">What is Lumen OHM?</a></br>
	<a href="<?php echo $imasroot; ?>/ohm/forms.php?action=resetpw">Forgot Password</a><br/>
	<a href="<?php echo $imasroot; ?>/ohm/forms.php?action=lookupusername">Forgot Username</a></br>
	<a href="<?php echo $imasroot; ?>/ohm/newinstructor.php?">Request an instructor account</a>
</div>
	</div>

	<footer class="footer">
		<p><?php echo $installname;?> is powered by <a href="http://www.imathas.com">IMathAS</a> &copy; 2006-2017 David Lippman |
		<a href="<?php echo $CFG['GET']['privacyPolicyPage']; ?>">Privacy Policy</a></p>
	</footer>
</div>
<?php 
	require(__DIR__ . "/../footer.php");
?>
