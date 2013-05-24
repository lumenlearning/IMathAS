<?php
//IMathAS Math Config File.  Adjust settings here!

//path settings
//web path to install
$imasroot = "";

//database access settings
$AWSkey = getenv('AWS_ACCESS_KEY_ID');
$AWSsecret = getenv('AWS_SECRET_KEY');
$AWSbucket = getenv('PARAM1');
$dbserver = getenv('PARAM2');
$dbname = getenv('PARAM3');
$dbusername = getenv('PARAM4');
$dbpassword = getenv('PARAM5');
if (getenv('imasroot')!==false) {
	$imasroot = getenv('imasroot');
}
$CFG['GEN']['AWSforcoursefiles'] = true;

//install name
$installname = "MyOpenMath";

//login prompts
$loginprompt = "Username";
$longloginprompt = "Enter a username.  Use only numbers, letters, or the _ character.";
$loginformat = '/^\w+$/';

//require email confirmation of new users?
$emailconfirmation = false;

//email to send notices from
$sendfrom = "do-not-reply@myopenmath.com";
$accountapproval = "admin@myopenmath.com";


//color shift icons as deadline approaches?
$colorshift = true;



//mimetex path
$mathimgurl = "/cgi-bin/mimetex.cgi";

//enable lti?
$enablebasiclti = true;

//allow nongroup libs?
$allownongrouplibs = false;

//allow course import of questions?
$allowcourseimport = false;

//allow macro install?
$allowmacroinstall = true;

$templateuser = 2;

//userid for instructor on student self-enroll courses
 $CFG['GEN']['selfenrolluser'] = 13;

 //allow instructors to create student accounts?
 $CFG['GEN']['allowinstraddstus'] = false;
 //allow instructors to enroll tutors?
 $CFG['GEN']['allowinstraddtutors'] = true;
 //minimum rights required to add/remove teachers to a course
 $CFG['GEN']['addteachersrights'] = 40;
 $CFG['GEN']['homelayout'] = '|0,1,2|10,11|0,1';
 $CFG['GEN']['headerinclude'] = "headercontent.php";
 $CFG['GEN']['headerscriptinclude'] = "momga.js";
 
 //do safe course delete
 $CFG['GEN']['doSafeCourseDelete'] = true;
	 
 //can set almost any assessment setting this way
 $CFG['AMS']['defpoints'] = 1;
 $CFG['AMS']['showtips'] = 2;

 $CFG['AMS']['guesslib'] = true;
	 
 //and most of the gradebook settings
 $CFG['GBS']['defgbmode'] = 1011;
 $CFG['GBS']['orderby'] = 1;
 $CFG['GBS']['lockheader'] = true;
 
 //and course settings.  All but themelist are in the form
 //array(defvalue, allowchange)
 $CFG['CPS']['hideicons'] = array(0,0);
 $CFG['CPS']['cploc'] = array(7,0);
 $CFG['CPS']['picicons'] =  array(1,0);
 $CFG['CPS']['unenroll'] = array(0,0);
 $CFG['CPS']['chatset'] = array(0,0);
 $CFG['CPS']['showlatepass'] = array(1,0);
 $CFG['CPS']['topbar'] = array(array("0,1,2,3,9","0,2,3,4,6,7,9",1),0);
 
// $CFG['CPS']['leftnavtools'] = false;
 $CFG['CPS']['templateoncreate'] = true;
 
 $defaultcoursetheme = "otbs_fw.css";
 $CFG['CPS']['theme'] = array("otbs_fw.css",1);
 $CFG['CPS']['themelist'] ="otbs_fw.css,otbs.css,modern.css,angelish.css,facebookish.css";
 $CFG['CPS']['themenames'] = "Lumen Fixed Width,Lumen Fluid,Modern,Clean,Social";
	 
 $CFG['TE']['navicons'] = array(
	 'untried'=>'te_blue_arrow.png',
	 'canretrywrong'=>'te_red_redo.png',
	 'canretrypartial'=>'te_yellow_redo.png',
	 'noretry'=>'te_blank.gif',
	 'correct'=>'te_green_check.png',
	 'wrong'=>'te_red_ex.png',
	 'partial'=>'te_yellow_check.png');

 $CFG['GEN']['sendquestionproblemsthroughcourse'] = 1;
 $CFG['GEN']['badgebase'] = 'badgebasemom.png';

 $CFG['GEN']['guesttempaccts'] = array(518);
 
 $CFG['GEN']['forcecanvashttps'] = true;
 $CFG['GEN']['addwww'] = true;
 $CFG['GEN']['TOSpage'] = $imasroot.'/termsofuse.php';
 $CFG['GEN']['enrollonnewinstructor'] = array(1,11);
 $smallheaderlogo = '<img src="'.$imasroot.'/img/collapse.gif"/>';
 $CFG['GEN']['logopad'] = '20px';
 //$studentTOS = "studentTOS.php";
 
//Math chat disabled 
//$mathchaturl = "$imasroot/mathchat/index.php";

//session path 
//$sessionpath = "";

 error_reporting(0);


//Uncomment to change the default course theme, also used on the home & admin page:
//$defaultcoursetheme = "default.css"

//To change loginpage based on domain/url/etc, define $loginpage here

//no need to change anything from here on
if (isset($CFG['CPS']['theme'])) {
  	  $coursetheme = $CFG['CPS']['theme'][0];
  } else if (isset($defaultcoursetheme)) {
  	  $coursetheme = $defaultcoursetheme;
  }
  /* Connecting, selecting database */
  if (!isset($dbsetup)) {
	 $link = mysql_connect($dbserver,$dbusername, $dbpassword) 
	  or die("<p>Could not connect : " . mysql_error() . "</p></div></body></html>");
	 mysql_select_db($dbname) 
	  or die("<p>Could not select database</p></div></body></html>");
	  
	  unset($dbserver);
	  unset($dbusername);
	  unset($dbpassword);
  }
  //clean up post and get if magic quotes aren't on
  function addslashes_deep($value) {
	return (is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value));
  }
  if (!get_magic_quotes_gpc()) {
   $_GET    = array_map('addslashes_deep', $_GET);
   $_POST  = array_map('addslashes_deep', $_POST);
   $_COOKIE = array_map('addslashes_deep', $_COOKIE);
  }

?>
