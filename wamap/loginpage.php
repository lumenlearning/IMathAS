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
	require("../header.php");
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
	 
	 $pref = 0;
	 /*
	 if (isset($_COOKIE['mathgraphprefs'])) {
		 $prefparts = explode('-',$_COOKIE['mathgraphprefs']);
		 if ($prefparts[0]==2 && $prefparts[1]==2) { //img all
			$pref = 3;	 
		 } else if ($prefparts[0]==2) { //img math
			 $pref = 4;
		 } else if ($prefparts[1]==2) { //img graph
			 $pref = 2;
		 }
			 
	 }*/
?>
	


<div id="loginbox">
<form method="post" action="<?php echo $_SERVER['PHP_SELF'].$querys;?>" onsubmit="hashpw()">

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
<table>
<tr><td><?php echo $loginprompt;?>:</td><td><input type="text" size="15" id="username" name="username" /></td></tr>
<tr><td>Password:</td><td><input type="password" size="15" name="password" /></td></tr>
</table>
<div id="settings">JavaScript is not enabled.  JavaScript is required for <?php echo $installname; ?>.  Please enable JavaScript and reload this page</div>
<div class="textright"><a href="<?php echo $imasroot; ?>/forms.php?action=newuser">Register as a new student</a></div>
<div class="textright"><a href="<?php echo $imasroot; ?>/forms.php?action=lookupusername">Forgot Username</a><br/>
<a href="<?php echo $imasroot; ?>/forms.php?action=resetpw">Forgot Password</a></div>
<div class="textright"><a href="<?php echo $imasroot; ?>/checkbrowser.php">Browser check</a></div>
<input type="hidden" id="tzoffset" name="tzoffset" value="" />
<input type="hidden" id="tzname" name="tzname" value=""> 
<input type="hidden" id="challenge" name="challenge" value="<?php echo $challenge; ?>" />
<script type="text/javascript">        
        var thedate = new Date();  
        document.getElementById("tzoffset").value = thedate.getTimezoneOffset(); 
        var tz = jstz.determine(); 
        document.getElementById("tzname").value = tz.name();
</script> 


<script type="text/javascript"> 
	 function updateloginarea() {
		setnode = document.getElementById("settings"); 
		var html = ""; 
		html += 'Accessibility: ';
		html += "<a href='#' onClick=\"window.open('<?php echo $imasroot;?>/help.php?section=loggingin','help','top=0,width=400,height=500,scrollbars=1,left='+(screen.width-420))\">Help<\/a>";
		html += '<div style="margin-top: 0px;margin-right:0px;text-align:right;padding:0px"><select name="access"><option value="0">Use defaults</option>';
		html += '<option value="3">Force image-based display</option>';
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
		html += '<div class="textright"><input type="submit" value="Login" /><\/div>';
		//document.cookie = "test=test";
		//if (document.cookie.indexOf('test')!=-1) {
			setnode.innerHTML = html; 
			document.getElementById("username").focus();
		//} else {
		//	setnode.innerHTML = 'Cookies are not enabled.  Session cookies are needed to track your session.';
		//}
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
<p class="textright">WAMAP is powered by <a href="http://www.imathas.com">IMathAS</a> &copy; 2006-2016 David Lippman<br/>
<a href="/wamap/info/credits.php">Credits</a></p>
</div>
<?php 
	require("../footer.php");
?>
