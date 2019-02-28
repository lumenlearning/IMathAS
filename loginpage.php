<?php
if (!isset($imasroot)) { //don't allow direct access to loginpage.php
	header("Location: index.php");
	exit;
}
//any extra CSS, javascript, etc needed for login page
	$placeinhead = "<link rel=\"stylesheet\" href=\"$imasroot/infopages.css?v=063017\" type=\"text/css\" />\n";
	$placeinhead .= "<script type=\"text/javascript\" src=\"$imasroot/javascript/jstz_min.js\" ></script>";

	$nologo = true;
	require("header.php");
	if (!empty($_SERVER['QUERY_STRING'])) {
		 $querys = '?'.$_SERVER['QUERY_STRING'];
	 } else {
		 $querys = '';
	 }
	 $loginFormAction = $GLOBALS['basesiteurl'] . substr($_SERVER['SCRIPT_NAME'],strlen($imasroot)) . Sanitize::encodeStringForDisplay($querys);

	 if (!empty($_SESSION['challenge'])) {
		 $challenge = $_SESSION['challenge'];
	 } else {
		 //use of microtime guarantees no challenge used twice
		 $challenge = base64_encode(microtime() . rand(0,9999));
		 $_SESSION['challenge'] = $challenge;
	 }
	 $pagetitle = "Welcome";
	 include("infoheader.php");

?>



<div id="loginbox">
<form method="post" action="<?php echo $loginFormAction;?>">
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
<tr><td></td><td><input type="submit" value="Login"></td></tr>
</table>

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

<p class="mainhdr">Free and Open</p>

<p class="subhdr">Students</p>
<p class="ind">Are you a student looking to study mathematics on your own, and want to
do exercises with immediate feedback as you work through a free and open textbook?
Then read more about our <a href="<?php echo $imasroot;?>/info/selfstudy.php">self study courses</a>.</p>

<p class="subhdr">Instructors</p>
<p class="ind">Are you an instructor who wants to adopt an open textbook, who feels
online interactive homework is valuable, but doesn't want their students to have
to pay an additional fee?
Then read more about <a href="<?php echo $imasroot;?>/info/classroom.php">using MyOpenMath in the classroom</a>.</p>

<p class="subhdr">Getting Started</p>
<p class="ind">If you already have an account, you can log on using the box to the right.</p>
<p class="ind">If you are a new student to the system, <a href="<?php echo $imasroot; ?>/forms.php?action=newuser">register as a new student</a></p>
<p class="ind">If you are an instructor, you can <a href="<?php echo $imasroot;?>/newinstructor.php">request an instructor account</a></p>


<br class=clear>
<p>&nbsp;</p>
<p class="textright"><?php echo $installname;?> is powered by <a href="http://www.imathas.com">IMathAS</a> &copy; 2006-2019 David Lippman<br/>
with financial support from <a href="https://www.lumenlearning.com">Lumen Learning</a> and <a href="https://www.xyzhomework.com/">xyzHomework</a><br/>
<a href="<?php echo $imasroot;?>/info/privacy.php">Privacy Policy</a> |
<a href="https://docs.google.com/document/d/1vS2LLJSsoW6v9qa3P_ru5dv9NfmNDJGijZtBJp_eGEM/edit?usp=sharing">Accessibility</a>
</p>
</div>
<?php
	require("footer.php");
?>
