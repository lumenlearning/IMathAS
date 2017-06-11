<?php
$nologo = true;
require("../init_without_validate.php");
$placeinhead = "<link rel=\"stylesheet\" href=\"$imasroot/infopages.css\" type=\"text/css\">\n";
$placeinhead .= '<style type="text/css">ul.spaced li {padding-bottom: .5em;}</style>';
require("../header.php");
$pagetitle ="For Instructors";
require("../infoheader.php");
?>

<img class="floatleft" src="<?php echo $imasroot;?>/img/screens.jpg"/>

<div class="content">

<h2>Classroom Use</h2>
<p>MyOpenMath is designed for mathematics, providing delivery of homework, quizzes, and tests 
with rich mathematical content.  Students can receive immediate feedback on algorithmically 
generated questions with numerical or algebraic expression answers.  And it can do so much
more, providing a full course management system, including file posting, discussion forums,
and a full gradebook, all designed with mathematics in mind.
</p>

<p>MyOpenMath can be used to web-enhance an on-campus course, as part of hybrid course, or to
run a fully online course.  To get some idea how the system can be used by instructors,
watch this <a href="http://www.youtube.com/watch?v=jKfLVPA_KNs">quick three minute video</a>

<p>MyOpenMath provides pre-built courses based on popular open textbooks.  Some of these
course only include online homework, while others include videos, handouts, and instructor resources.
The books for these courses can be read online, or printed copies can be ordered through our partner
site, <a href="http://www.opentextbookstore.com">OpenTextBookStore.com</a>.</p>
<p>We currently have pre-built courses aligned with:
<ul class="spaced">
  <li>Arithmetic for College Students, David Lippman's remix of the MITE/NROC textbook</li>
  <li>Prealgebra, by College of the Redwoods</li>
  <li>Beginning and Intermediate Algebra, by Tyler Wallace</li>
  <li>Beginning and Intermediate Algebra, a CK12 flexbook remixed by James Sousa</li>
  <li>Math in Society, by David Lippman (A quantitative reasoning / math for liberal arts course)</li>
  <li>Precalculus: an Investigation of Functions, by David Lippman and Melonie Rasmussen<br/>(covers College Algebra and Trig) <a href="http://www.youtube.com/watch?v=1IpDmCaJ6rI" target="_blank">video tour</a></li>
  <li>College Algebra, by Carl Stitz and Jeff Zeager</li>
  <li>Business Calculus, by Shana Calaway, Dale Hoffman, and David Lippman</li>
  <li>Contemporary Calculus (single variable), by Dale Hoffman</li>
</ul>
</p>
<p>To preview these courses, <a href="../index.php">return to the login page</a> and login with username <b>guest</b> (no password needed).</p>
</div>

</body>
</html>
