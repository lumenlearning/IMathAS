<?php
$nologo = true;
require("../init_without_validate.php");
$placeinhead = "<link rel=\"stylesheet\" href=\"$imasroot/infopages.css\" type=\"text/css\">\n";
require("../header.php");
$pagetitle ="Self Study";
require("../infoheader.php");
?>

<img class="floatleft" src="<?php echo $imasroot;?>/img/mathpaper.jpg"/>

<div class="content">

<h2>Self Study</h2>
<p>While most students use MyOpenMath in connection with an instructor-led course, 
MyOpenMath can be used by students as a self-study resource.  MyOpenMath provides 
interactive assessment to supplement several
free and open math textbooks that can be read online, or printed copies can be ordered through our partner
site, <a href="http://www.opentextbookstore.com">OpenTextBookStore.com</a>.  All our self-study
courses include online interactive, self-grading assessment that can automatically create new versions of 
problems to provide unlimited practice with instant feedback.  Some include video lessons.</p>

<p>Want to know more?  Check out this <a href="http://www.youtube.com/watch?v=jCmmHH3b4Vw">short overview video</a>.</p>

<p>To enroll in a self-study course, <a href="<?php echo $imasroot; ?>/forms.php?action=newuser">register as a new student</a>, 
and select the self-study course you wish
to take when you sign up.  To add an additional class later, click the "Enroll in a new Course" button after 
logging in.</p>

<p>We currently have self-study courses for:
<ul>
  <li>Prealgebra, using the <a href="http://www.opentextbookstore.com/details.php?id=5">College of Redwoods</a> textbook</li>
  <li>Beginning and Intermediate Algebra, using the <a href="http://www.opentextbookstore.com/details.php?id=6">Tyler Wallace</a> textbook</li>
  <li>Precalculus 1 (College Algebra), using the <a href="http://www.opentextbookstore.com/details.php?id=2">Lippman/Rasmussen</a> textbook</li>
  <li>Precalculus 2 (Trigonometry), using the <a href="http://www.opentextbookstore.com/details.php?id=2">Lippman/Rasmussen</a> textbook</li>
  <li>Trigonometry, using the <a href="https://dl.dropboxusercontent.com/u/28928849/TrigCh1/CK-12-TrigSecond-Edition.pdf">CK12/Sousa</a> textbook</li>
</ul>
</p>
 
</div>

</body>
</html>
