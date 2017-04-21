<?php
if (!isset($imasroot)) { //don't allow direct access to loginpage.php
	header("Location: index.php");
	exit;
}
//any extra CSS, javascript, etc needed for login page
	$placeinhead = "<link rel=\"stylesheet\" href=\"$imasroot/wamap/infopages.css\" type=\"text/css\" />\n";
	$placeinhead .= "<script type=\"text/javascript\" src=\"$imasroot/javascript/jstz_min.js\" ></script>";
	$nologo = true;
	$flexwidth = true;
	require(dirname(__FILE__) ."/../header.php");
	if (isset($_SERVER['QUERY_STRING'])) {
		 $querys = '?'.$_SERVER['QUERY_STRING'];
	 } else {
		 $querys = '';
	 }
	 if (!empty($_SESSION['challenge'])) {
		 $challenge = $_SESSION['challenge'];
	 } else {
		 $challenge = base64_encode(microtime() . rand(0,9999));
		 $_SESSION['challenge'] = $challenge;
	 }
	 $pagetitle = "About Us";

	 $wamapdir = rtrim(dirname(__FILE__), '/\\');

	 include("$wamapdir/infoheader.php");

?>



<div id="loginbox">
<form method="post" action="<?php echo $_SERVER['PHP_SELF'].$querys;?>">

<?php
	if ($haslogin) {
		if ($badsession) {
			echo '<p>Unable to establish a session.  Check that your browser is set to allow session cookies</p>';
		} else {
			echo "<p>Login Error.  Try Again</p>\n";
		}
	}
	if ($err!='') {
		echo $err;
	}
?>
<b>Login</b>

<div><noscript>JavaScript is not enabled.  JavaScript is required for <?php echo $installname; ?>.  Please enable JavaScript and reload this page</noscript></div>

<table>
<tr><td><label for="username"><?php echo $loginprompt;?></label>:</td><td><input type="text" size="15" id="username" name="username" /></td></tr>
<tr><td><label for="password">Password</label>:</td><td><input type="password" size="15" id="password" name="password" /></td></tr>
</table>
<div class="textright"><input type="submit" value="Login" /></div>
<div class="textright"><a href="<?php echo $imasroot; ?>/forms.php?action=newuser">Register as a new student</a></div>
<div class="textright"><a href="<?php echo $imasroot; ?>/forms.php?action=resetpw">Forgot Password</a><br/>
<a href="<?php echo $imasroot; ?>/forms.php?action=lookupusername">Forgot Username</a></div>
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
</div>
<div class="text">

<p>WAMAP is a web based mathematics assessment and course management platform.  Its use is provided free to Washington State public
educational institution students and instructors.
 </p>
 <img style="float: left; margin-right: 20px;" src="<?php echo $imasroot; ?>/wamap/img/screens.jpg" alt="Computer screens"/>

<p>This system is designed for mathematics, providing delivery of homework, quizzes, tests, practice tests,
and diagnostics with rich mathematical content.  Students can receive immediate feedback on algorithmically generated questions with
numerical or algebraic expression answers.
</p>

<p>If you already have an account, you can log on using the box to the right.</p>
<p>If you are new to WAMAP, use the links above to find information about using WAMAP in the classroom, or to access diagnostic assessments.</p>
<br class="clear" />
<p class="textright">WAMAP is powered by <a href="http://www.imathas.com">IMathAS</a> &copy; 2006-2017 David Lippman<br/>
<a href="<?php echo $imasroot;?>/wamap/privacy.php">Privacy</a> | 
<a href="https://docs.google.com/document/d/1vS2LLJSsoW6v9qa3P_ru5dv9NfmNDJGijZtBJp_eGEM/edit?usp=sharing">Accessibility</a> |
<a href="/wamap/info/credits.php">Credits</a></p>
</div>
<?php
	require(dirname(__FILE__) ."/../footer.php");
?>
