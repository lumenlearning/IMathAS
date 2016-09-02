<?php
$nologo = true;  $flexwidth = true; $loadinginfoheader = true;
$placeinhead = "<link rel=\"stylesheet\" href=\"$imasroot/wamap/infopages.css\" type=\"text/css\">\n";
require("../header.php");
$pagetitle =$line['name'];
require("infoheader.php");
?>
<div style="margin-left: 10px">
<p>
The Math Placement Test (MPT) is a series of tests used to help place college-bound students into the correct mathematics course.  There are
currently three MPT tests given:
<ul>
<li>MPT-General (MPT-G), also known as the College Readiness Math Test.  This test is aligned with the
<a href="http://www.transitionmathproject.org/standards/index.asp">Washington state College Readiness Mathematics Standards</a>, and is
intended to show that a student is ready for <i>some</i>, but not necessarily all, college level math courses.</li>
<li>MPT-Intermediate (MPT-I), is intended to show that students are ready for pre-calculus level mathematics courses.</li>
<li>MPT-Advanced (MPT-A), is intended to show that students are ready for calculus-level mathematics courses.</li>
</ul>
</p>

<p>This site provides practice problems for the MPT-G test.  While students planning to take the MPT-I or MPT-A tests may find this practice
useful, there are topics covered on the intermediate and advanced tests that are not covered here.</p>

<p>This site is <i>not</i> created by or officially endorsed by UW-OEA, the administrators of the MPT tests.  For more information about the
tests, and to see some official sample problems, you are encouraged to visit the
<a href="http://www.washington.edu/oea/services/testing_center/mpt.html">official MPT website</a>.</p>

<p>To begin your practice, enter a phone number and your name below.  You will receive a randomly-selected set of practice questions
representing the general areas of content that appear on the MPT-G.  While the general areas of content are the same, you should not
expect to questions on the actual MPT-G to necessarily be similar to the questions you see in this practice.</p>

<p><b>To begin your practice:</b></p>
<form method=post action="../diag/index.php?id=<?php echo $diagid; ?>">
<span class=form><?php echo $line['idprompt']; ?></span> <input class=form type=text size=12 name=SID><BR class=form>
<span class=form>Enter First Name:</span> <input class=form type=text size=20 name=firstname><BR class=form>
<span class=form>Enter Last Name:</span> <input class=form type=text size=20 name=lastname><BR class=form>
<span class="form">Which practice would you like to work on?</span>
	<span class="formright">

	<div style="margin-left: 1.5em; margin-top:0px; margin-bottom: 5px; margin-right: 0px;"><input type="radio" name="course" value="2" checked="checked" style="margin-left: -2em;"/>Multiple Choice, timed, one attempt per question.  This version is most like the actual MPT-G in format.</div>
	<div style="margin-left: 1.5em; margin-top:0px; margin-bottom: 5px; margin-right: 0px;"><input type="radio" name="course"  value="1" style="margin-left: -2em;"/>Multiple Choice, unlimited attempts.  This version is intended for extended practice.  It is in the same multiple-choice format as the actual MPT-G, but this version allows unlimited attempts at each question, is not timed, and can be continued at a later date.</div>
	<div style="margin-left: 1.5em; margin-top:0px; margin-bottom: 5px; margin-right: 0px;"><input type="radio" name="course" value="0" style="margin-left: -2em;"/>Free Response, unlimited attempts.   This version is intended for extended practice.   This version of the practice test is not multiple-choice.  You may find practicing with free response questions useful, as it will force you to work each problem completely without gaining any hints from provided answer choices.</div>

	</span><br class=form>


<span class=form>Tell us a little about who you are:</span><span class=formright>
<select name="teachers" id="teachers">
<option value="UN" selected="selected">Not Specified</option>
<option value="HSF">High School freshman or sophomore</option>
<option value="HSJ">High School junior</option>
<option value="HSS">High School senior</option>
<option value="Ret">Returning to college after time away</option>
<option value="Col">Current college student</option>
<option value="Par">Parent</option>
<option value="HST">High School teacher</option>
<option value="ColT">College faculty</option>
</select></span><br class="form">


<?php
	if (!$noproctor) {
		echo "<b>This test can only be accessed from this location with an access password</b></br>\n";
		echo "<span class=form>Access password:</span>  <input class=form type=password size=40 name=passwd><BR class=form>";
	}
?>
<input type=hidden id=tzoffset name=tzoffset value="">
<script>
  var thedate = new Date();
  document.getElementById("tzoffset").value = thedate.getTimezoneOffset();
</script>
<div id="submit" class="submit" style="display:none"><input type=submit value='Begin Practice'></div>
<input type=hidden name="mathdisp" id="mathdisp" value="2" />
<input type=hidden name="graphdisp" id="graphdisp" value="2" />
</form>

<div id="bsetup">JavaScript is not enabled. JavaScript is required for <?php echo $installname; ?>. Please enable JavaScript and reload this page</div>

<script type="text/javascript">
function determinesetup() {
	document.getElementById("submit").style.display = "block";
	if (!AMnoMathML && !ASnoSVG) {
		document.getElementById("bsetup").innerHTML = "Browser setup OK";
	} else {
		document.getElementById("bsetup").innerHTML = "Using image-based display";
	}
	if (!AMnoMathML) {
		document.getElementById("mathdisp").value = "1";
	}
	if (!ASnoSVG) {
		document.getElementById("graphdisp").value = "1";
	}
}
var existingonload = window.onload;
if (existingonload) {
	window.onload = function() {existingonload(); determinesetup();}
} else {
	window.onload = determinesetup;
}
</script>

<hr/>
<p style="font-size:70%;">Privacy notice: The information you provide here is used only to maintain a record of your practice so that you can continue your practice later.  No
information provided will be used to contact you or be shared with third parties.</p><div class=right style="font-size:70%;">Built on <a href="http://imathas.sourceforge.net">IMathAS</a> &copy; 2006 David Lippman</div>
</div>
</body>
</html>
