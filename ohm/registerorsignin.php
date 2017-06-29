<?php
require("../init_without_validate.php");
require("../header.php");
?>
<script type="text/javascript" src="<?php echo $imasroot; ?>/ohm/js/floatinglabel.js"></script>
<?php
echo "<head><link rel='stylesheet' href='$imasroot/ohm/forms.css' type='text/css'></head>";
echo "<head><link rel='stylesheet' href='$imasroot/ohm/loginandenroll.css' type='text/css'></head>";

if($_GET['cid'] && $_GET['ekey']){
  $cid = Sanitize::courseId($_GET['cid']);
  $ekey = Sanitize::encodeStringForDisplay($_GET['ekey']);
}
else{
  $cid = Sanitize::courseId($_POST['courseid']);
  $ekey = Sanitize::encodeStringForDisplay($_POST['ekey']);
}
$hiddeninput = "<input  type='hidden'   name='cid'   value='$cid'/>
<input  type='hidden'   name='ekey'       value='$ekey'/>";
$username_placeholder ='"Enter a username (letters, numbers, _ )"';
$confirm_password='"Confirm Password"';
$choose_password ='"Choose a Password"';

if(($_POST['courseid'] && $_POST['ekey'] && $_POST['verified'] || $_GET['cid'] && $_GET['ekey'])){
  echo "
  <div class='lumensignupforms'>
  <ol class='wizard-progress clearfix'>
  <li class='active-step notlast'>
  <span class='visuallyhidden'>Step </span><span class='step-num'><a href='$imasroot/ohm/enroll.php'>1</a></span>
  </li>
  <li class='active-step notlast'>
  <span class='visuallyhidden'>Step </span><span class='step-num'><a href='$imasroot/ohm/verifyenrollinfo.php?cid=$cid&ekey=$ekey'>2</a></span>
  </li>
  <li class='active-step'>
  <span class='visuallyhidden'>Step </span><span class='step-num'>3</span>
  </li>
  </ol>
  <div class='row'>
  <div id=headerforms class='left-align'>
  <center><h2>Sign In</h2>
  <p class='marginHeaderp'>Already have an OHM account?</p></center>
  <div class='login-wrapper'>"
  ?>
  <?php
  if ($_GET['relogin']) {
    echo "<p class=\"error-msg\">Oops! Wrong username or password. Please Re-enter</p>\n";
  }
  ?>
  <?php
  echo"<div id='loginbox'>
  <form action='$imasroot/actions.php?action=enroll' method=post>
  $hiddeninput
  <input  type='hidden'   name='enrollandlogin'   value='enrollandlogin'/>
  <div class='enroll field'>
    <label for='username'>Username</label>
    <input class='lumenform form inputText' type='text' id='username' name='username' placeholder='Username' aria-label='Username' />
  </div>
  <div class='enroll field'>
    <label for='p'>Password</label>
    <input class='lumenform form inputText' type='password' id='password' name='password' placeholder='Password'  aria-label='Password' />
  </div>
  <center><button class='button' type='submit'>Login</button></center>
  </form>
  </div>
  </div>
  </div>
  <div id=headerforms class='right-align'>
  <center><h2>Sign up</h2>
  <p class='marginHeaderp'>New to OHM? Sign up </p></center>
  <form action=$imasroot/actions.php?action=newuser method=post>
  <input  type='hidden'   name='enrollandregister'       value='enrollandregister'/>";
  echo "<input  class='lumenform form' type='hidden'  name='courseid' placeholder='Course Id' value= $cid  aria-label='courseid' />
  <input class='lumenform form' type='hidden' name='ekey' placeholder='Enrollment Key'  value=$ekey aria-label='Enrollment Key:' />
  $hiddeninput";
  echo "
  <div class='enroll field'>
    <label for='SID'>Enter a username (letters, numbers, _ )</label>
    <input class='lumenform form inputText' class=\"form\" type=\"text\"  id=SID name=SID placeholder= ".$username_placeholder ." aria-label=Enter Username required>\n
  </div>
  <div class='enroll field'>
    <label for='pw1'>Choose a Password</label>
    <input class='lumenform form inputText' type=\"password\"  id=pw1 name=pw1 placeholder= ".$choose_password ." aria-label=Password required>\n
  </div>
  <div class='enroll field'>
    <label for='pw2'>Confirm Password</label>
    <input class='lumenform form inputText' type=\"password\"  id=pw2 name=pw2 placeholder= ".$confirm_password ." aria-label=Password required>\n
  </div>
  <div class='enroll field'>
    <label for='firstname'>Firstname</label>
    <input class='lumenform form inputText' type=\"text\" size=20 id=firstname name=firstname placeholder=Firstname  aria-label=firstname required>\n
  </div>
  <div class='enroll field'>
    <label for='lastname'>Lastname</label>
    <input class='lumenform form inputText' type=\"text\" size=20 id=lastname name=lastname placeholder=Lastname  aria-label=lastname required>\n
  </div>
  <div class='enroll field'>
    <label for='email'>Email</label>
    <input class='lumenform form inputText' type=\"text\"  id=email name=email placeholder=Email  aria-label=email required>\n
  </div>
  <br/>
  <label class=form>
  <input type=checkbox id=msgnot name=msgnot checked=checked aria-label=msgnot />
  <span>Notify me by email when I receive a new message</span>
  </label>";
  if (isset($studentTOS)) {
    // echo "<span class=form><label for=\"agree\"></label></span><span class=formright></span><br class=form />\n";
    echo "
    <label class=form >
    <input type=checkbox name=agree id=agree  aria-label=agree required/>
    <span>I have read and agree to the Terms of Use (below)</span>
    </label>";
  } else if (isset($CFG['GEN']['TOSpage'])) {
    echo "
    <label class=form>
    <input type=checkbox name=agree id=agree aria-label=agree  required/>
    <span>I have read and agree to the <a href=\"#\" onclick=\"GB_show('Terms of Use','".$CFG['GEN']['TOSpage']."',700,500);return false;\">Terms of Use</a></span>
    </label>";
  }
  if ($doselfenroll) {
    echo '<div id="selfenrollwarn" class=noticetext style="display:none;">Warning: You have selected a non-credit self-study course. ';
    echo 'If you are using '.$installname.' with an instructor-led course, this is NOT what you want; nothing you do in the self-study ';
    echo 'course will be viewable by your instructor or count towards your course.  For an instructor-led ';
    echo 'course, you need to enter the course ID and key provided by your instructor.</div>';
  }
  echo "<button class=button type=submit>Submit</button>
  </form>
  </div>";
  if (isset($studentTOS)) {
    include($studentTOS);
  }
  "</form>
  </div>
  </div>
  </div>

  ";
}

?>
