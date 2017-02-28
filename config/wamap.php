<?php

/*** WAMAP.org config ***/

$CFG['GEN']['directaccessincludepath'] = 'wamap/';
$CFG['GEN']['diagincludepath'] = '../wamap/';
$loginpage = 'wamap/loginpage.php';


$installname = "WAMAP";
$longloginprompt = "Enter a username.  Use only numbers, letters, or the _ character.";
$loginprompt = "Username";
$loginformat = '/^\w+$/';  //A-Z, a-z, 0-9, _ are the only allowed characters
$emailconfirmation = false;
$sendfrom = "do-not-reply@wamap.org";
$newacctemail = "dlippman@pierce.ctc.edu";
$accountapproval = "dlippman@pierce.ctc.edu";
$colorshift = true;
$smallheaderlogo = '<img src="/wamap/img/wamaplogosmall.gif" alt="Show courses list"/>';
$allownongrouplibs = false;
$allowcourseimport = false;
$enablebasiclti = true;
//$mathchaturl = "http://www.imathas.com/cur/mathchat/index.php";

//user for course templates
$templateuser = 890;

//special configs
$CFG['GEN']['allowInstrImportStuByName'] = false;
$CFG['CPS']['cploc'] = array(7,0);
$CFG['GBS']['orderby'] = 1;
$CFG['GEN']['sendquestionproblemsthroughcourse'] = 1;
$CFG['GEN']['allowteacherexport'] = 1;
$CFG['GEN']['LTIorgid'] = 'www.wamap.org';

$CFG['CPS']['chatset'] = array(0,0);
$CFG['CPS']['hideicons'] = array(0,0);
$CFG['CPS']['picicons'] =  array(1,0);
$CFG['CPS']['unenroll'] = array(0,0);
$CFG['CPS']['showlatepass'] = array(1,0);
$CFG['CPS']['topbar'] = array(array("0,1,2,3,9","0,2,3,4,6,9",1),0);
$CFG['CPS']['templateoncreate'] = true;

//and most of the gradebook settings
$CFG['GBS']['defgbmode'] = 1011;
$CFG['GBS']['orderby'] = 1;

$CFG['GEN']['skipbrowsercheck'] = true;

$CFG['GEN']['meanstogetcode'] = 'requesting an instructor account on wamap.org';

$CFG['GEN']['homelayout'] = '|0,1,2|10,11|0,1';
$CFG['GEN']['noimathasimportfornonadmins'] = true;

$CFG['AMS']['defpoints'] = 1;
$CFG['AMS']['showtips'] = 2;
$CFG['AMS']['eqnhelper'] = 4;

$defaultcoursetheme = "wamap.css";
$CFG['CPS']['theme'] = array("wamap.css",1);
//$CFG['CPS']['themelist'] ="angelish.css,angelish_fw.css,angelishgreen.css,angelishpurple.css,default.css,facebookish.css,modern.css,halloween3_dark,";
//$CFG['CPS']['themenames'] = "WAMAP Standard,WAMAP Standard Fixed Width,WAMAP Standard Green,WAMAP Standard Purple,OldSchool Retro,Social,Clean,Halloween,";

$CFG['GEN']['headerinclude'] = "headercontentwamap.php";
$CFG['GEN']['hidedefindexmenu'] = true;

$CFG['GEN']['mathjaxonly'] = true;
//$CFG['GEN']['translatewidgetID'] = '4c87c0627e615711-207414b9ebceeffe-g2defaf4d45bf3a67-d';

$CFG['FCM'] = array(
    'SenderId' => '994085988951',
    'webApiKey' => 'AIzaSyCPYLTUn1kFIU3BjP2wMP07FiSmzpTwpd4',
    'serverApiKey' => getenv('FCM_SERVER_KEY_W'),
    'icon' => '/wamap/img/large_icon.png'
);