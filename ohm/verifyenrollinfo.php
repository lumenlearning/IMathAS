<?php
require("../init_without_validate.php");
require("../header.php");

?>
<html>
<head>
  <?php echo "<link rel='stylesheet' href='$imasroot/ohm/forms.css' type='text/css'>";?>
  <?php echo "<link rel='stylesheet' href='$imasroot/ohm/loginandenroll.css' type='text/css'>";?>

</head>
<body>
<?php
if($_GET['cid'] && $_GET['ekey']  ){
  $_POST['courseid']  = $_GET['cid'];
  $_POST['ekey']      = $_GET['ekey'] ;
}
if($_POST['courseid'] && $_POST['ekey']){
  $stm = $DBH->prepare("SELECT name,enrollkey,allowunenroll,deflatepass,msgset,id FROM imas_courses WHERE id = :cid AND (available=0 OR available=2)");
  $stm->execute(array(':cid'=>$_POST['courseid']));
  $line = $stm->fetch(PDO::FETCH_ASSOC);

  $keylist = array_map('strtolower',array_map('trim',explode(';',$line['enrollkey'])));

  if (null==$line) {
   $message = 'Course not found';
  } else if (!in_array(strtolower($_POST['ekey']), $keylist)) {
   $message = "A course with that Course Id or Enrollment key Does not exist";
  } else if (($line['allowunenroll']&2)==2) {
   $message = "Course is closed for self enrollment.  Contact your instructor for access.\n";
  }else{
     $cid = Sanitize::courseId($_POST['courseid']);
     $ekey = Sanitize::encodeStringForDisplay($_POST['ekey']);
     $coursename= Sanitize::encodeStringForDisplay($line['name']);
     $stm = $DBH->prepare(" SELECT FirstName,LastName FROM imas_users JOIN imas_teachers ON imas_teachers.userid = imas_users.id WHERE imas_teachers.courseid = :cid;");
     $stm->execute(array(':cid'=>$cid));
     $line = $stm->fetch(PDO::FETCH_ASSOC);
     $FirstName= Sanitize::encodeStringForDisplay($line["FirstName"]);
     $LastName= Sanitize::encodeStringForDisplay($line["LastName"]);
     $Detailsmessage = "
      <p>Course Name: <span>$coursename</span></p>
       <p>Course Id:<span> $cid</span></p>
       <p>Course Enrollment Key: <span>$ekey</span></p>
       <p>Teacher <span>$FirstName $LastName</span></p>
       </div>";

  }
  echo "
        <div class='lumensignupforms'>
        <ol class='wizard-progress clearfix'>
             <li class='active-step notlast'>
             <span class='visuallyhidden'>Step </span><span class='step-num'><a href='$imasroot/ohm/enroll.php'>1</a></span>
             </li>
             <li class='active-step notlast'>
                 <span class='visuallyhidden'>Step </span><span class='step-num'>2</span>
             </li>
             <li>
                 <span class='visuallyhidden'>Step </span><span class='step-num'>3</span>
             </li>
         </ol>
        <div id=headerforms class=pagetitle><h2>Confirm your course selection below</h2></div>
        <div class='box-shadow'>
        <center><h3>Course Details</h3></center>
        $message
        $Detailsmessage";
       if($Detailsmessage){
         echo "<form action='$imasroot/ohm/registerorsignin.php' method='post'>
                 <input  type='hidden'   name='courseid'   value='$cid'/>
                 <input  type='hidden'   name='ekey'       value='$ekey'/><br/>
                 <input  type='hidden'   name='verified'       value='verified'/><br/>
                 <div class='enroll-div'><center><button  class='button left-align' type=submit>Enroll</button>
                 <a href='$imasroot/ohm/enroll.php' class='go-back'>Go Back</a><center></div>
                 </form>
                 </div>
              ";
       }else{
         echo "<br/> <br/></div><br/> <br/><center><a href='$imasroot/ohm/enroll.php' class='go-back'>Go Back</a><center></div>";
       }


}
?>
</body>
</html>
