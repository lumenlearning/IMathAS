<?php
$nologo = true; $flexwidth = true;
require("../../init_without_validate.php");
$placeinhead = "<link rel=\"stylesheet\" href=\"$imasroot/wamap/infopages.css\" type=\"text/css\">\n";
require("../../header.php");
$pagetitle ="Classroom";
require("../infoheader.php");
?>

<img class="floatleft" src="/wamap/img/mathpaper.jpg"/>

<div class="content">

<h2>For Students</h2>
<ul class="nomark">
<li>New to WAMAP?  <a href="/forms.php?action=newuser">Register as a new student</a>, then login. <br/>
Once logged in,
enter the Course ID and enrollment key provided by your instructor to sign up for your course</li>
<li><a href="/wamap/info/enteringanswers.php">Help with entering answers</a></li>
</ul>

<h2>For Instructors</h2>
<ul class="nomark">
<li><a href="/wamap/info/teachers.php">Get more info</a> about WAMAP</li>
<li><a href="/wamap/newinstructor.php">Request an instructor account</a></li>
<li><a href="/docs/docs.php">Documentation</a></li>
</ul>
</div>

</body>
</html>
