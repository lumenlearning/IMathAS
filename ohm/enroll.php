<?
require("../init_without_validate.php");
require("../header.php");

?>
<html>
<head>
  <script type="text/javascript" src="<?php echo $imasroot; ?>/ohm/js/floatinglabel.js"></script>
  <?php
     echo "<link rel='stylesheet' href='". $imasroot."/ohm/forms.css' type='text/css'>";
     echo "<link rel='stylesheet' href='". $imasroot ."/ohm/loginandenroll.css' type='text/css'>";
  ?>
</head>
<body>
<div class="lumensignupforms">
  <ol class="wizard-progress clearfix">
       <li class="active-step notlast">
           <span class="visuallyhidden">Step </span><span class="step-num">1</span>
       </li>
       <li class="notlast">
           <span class="visuallyhidden">Step </span><span class="step-num">2</span>
       </li>
       <li>
           <span class="visuallyhidden">Step </span><span class="step-num">3</span>
       </li>
   </ol>
<div id=headerforms class=pagetitle><h2>Enroll in a Course</h2></div>
<p class="marginp">Enter the Course ID and Enrollment Key provided by your instructor.</p>
<form action="<? echo $imasroot ?>/ohm/verifyenrollinfo.php" method="post">
  <div class="enroll field">
    <label for="courseid">Course Id</label>
    <input onfocus class="lumenform form inputText" type="text" placeholder="Course Id" name="courseid"  aria-label="courseid" required/>
  </div>
<div class="enroll field">
  <label for="ekey">Enrollment Key</label>
  <input class="lumenform form" type="text" name="ekey" placeholder="Enrollment Key"    required  aria-label="Enrollment Key:"/></br>
</div>
<div class="enroll-div"><center><button  class="button" type=submit>Enroll</button>
<a href='<? echo $imasroot?>' class='go-back'>Go Back</a><center></div>

</form>
</div>


</body>
</html>
