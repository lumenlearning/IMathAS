<?php
/**
 ************** IMPORTANT NOTICE ****************
 *
 * This is the PRODUCTION config file.
 * Any changes made here will affect PRODUCTION!
 *
 ************** IMPORTANT NOTICE ****************
 */

$loginpage = 'ohm/loginpage.php';

$installname = "Lumen OHM";

//static assets
$CFG['static_server'] = getenv('STATIC_ASSETS_BASE_URL') ?? 'https://ohm.lumenlearning.com/';

//aws config
$AWSbucket = "development" == $configEnvironment ? null : $_ENV['S3_MAIN_BUCKET_NAME'];

//login prompts
$loginprompt = "Username";
$longloginprompt = "Enter a username.  Use only numbers, letters, or the _ character.";
$loginformat = '/^[\w\.@\-]+$/';

//require email confirmation of new users?
$emailconfirmation = false;

//email to send notices from
$sendfrom = "do-not-reply@lumenlearning.com";
$accountapproval = "support@lumenlearning.com";

//use IPEDS IDs during new instructor signup?
$CFG['use_ipeds'] = true;

//this is for util/getstucntdetcsv2.php
$CFG['statsauth'] = getenv('STUDENT_DETAIL_REPORT_PASSWORD');

//color shift icons as deadline approaches?
$colorshift = true;

//enable lti?
$enablebasiclti = true;

//allow nongroup libs?
$allownongrouplibs = false;

//allow course import of questions?
$allowcourseimport = false;

// Lumen student payment authorization API
$GLOBALS['student_pay_api']['enabled'] = "true" == strtolower(getenv('STUPAY_ENABLED')) ? true : false;
$GLOBALS['student_pay_api']['base_url'] = getenv('STUPAY_API_BASE_URL');
$GLOBALS['student_pay_api']['timeout'] = getenv('STUPAY_API_TIMEOUT_SECS');
$GLOBALS['student_pay_api']['jwt_secret'] = getenv('STUPAY_API_JWT_SECRET');
$GLOBALS['student_pay_api']['trial_period_human'] = "14 days";
$GLOBALS['student_pay_api']['trial_extension_period_human'] = "48 hours";
$GLOBALS['student_pay_api']['access_code_min_length'] = 7;
$GLOBALS['student_pay_api']['access_code_max_length'] = 10;
$GLOBALS['student_pay_api']['trial_min_reminder_time_secs'] = 60 * 60 * 24; // 24 hour
$GLOBALS['student_pay_api']['direct_pay_component_url'] = getenv('DIRECT_PAY_COMPONENT_URL');
$GLOBALS['student_pay_api']['stripe_api_key'] = getenv('STRIPE_API_KEY');
$GLOBALS['student_pay_api']['debug'] = true;

//userid for instructor on student self-enroll courses
$CFG['GEN']['selfenrolluser'] = 13;

//allow instructors to create student accounts?
$CFG['GEN']['allowinstraddstus'] = false;
//allow instructors to enroll tutors?
$CFG['GEN']['allowinstraddtutors'] = true;
//minimum rights required to add/remove teachers to a course
$CFG['GEN']['addteachersrights'] = 40;
$CFG['GEN']['homelayout'] = '|0,1,2|10,11|0,1';
$CFG['GEN']['headerinclude'] = "ohm/headercontent.php";
$CFG['GEN']['footerscriptinclude'] = "ohm/js/lumenga.js";

$CFG['GEN']['noimathasimportfornonadmins'] = true;

// Override question answer shuffling.
if (!empty(getenv('NOSHUFFLE_ANSWERS'))) {
    $CFG['GEN']['noshuffle'] = getenv('NOSHUFFLE_ANSWERS');
}

//can set almost any assessment setting this way
$CFG['AMS']['defpoints'] = 1;
$CFG['AMS']['showtips'] = 2;
$CFG['AMS']['eqnhelper'] = 4;

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
$CFG['CPS']['topbar'] = array(array("0,1,2,3,9","0,2,3,4,6,9",1),0);

//$CFG['CPS']['leftnavtools'] = 'limited';
$CFG['CPS']['templateoncreate'] = true;

$defaultcoursetheme = "lumen.css";
$CFG['CPS']['theme'] = array("lumen.css_fw1920",0);
$CFG['CPS']['themelist'] ="lumen.css";
$CFG['CPS']['themenames'] = "Lumen Theme";

// add a course-level selector to the course settings page
$CFG['CPS']['usecourselevel'] = 'required';

$CFG['TE']['navicons'] = array(
    'untried'=>'te_blue_arrow.png',
    'canretrywrong'=>'te_red_redo.png',
    'canretrypartial'=>'te_yellow_redo.png',
    'noretry'=>'te_blank.gif',
    'correct'=>'te_green_check.png',
    'wrong'=>'te_red_ex.png',
    'partial'=>'te_yellow_check.png');

$CFG['CPS']['miniicons'] = array(
    'assess'=>'assess_tiny.png',
    'desmos'=>'../ohm/img/desmos_tiny.png',
    'drill'=>'assess_tiny.png',
    'inline'=>'inline_tiny.png',
    'linked'=>'html_tiny.png',
    'forum'=>'forum_tiny.png',
    'wiki'=>'wiki_tiny.png',
    'folder'=>'folder_tiny.png',
    'calendar'=>'1day.png');

$CFG['CPS']['itemicons'] = array (
    'folder' => 'folder2.gif',
    'foldertree' => 'folder_tree.png',
    'assess' => 'assess.png',
    'inline' => 'inline.png',
    'desmos' => '../ohm/img/desmos.png',
    'web' => 'web.png',
    'doc' => 'doc.png',
    'wiki' => 'wiki.png',
    'drill' => 'drill.png',
    'html' => 'html.png',
    'forum' => 'forum.png',
    'pdf' => 'pdf.png',
    'ppt' => 'ppt.png',
    'zip' => 'zip.png',
    'png' => 'image.png',
    'xls' => 'xls.png',
    'gif' => 'image.png',
    'jpg' => 'image.png',
    'bmp' => 'image.png',
    'mp3' => 'sound.png',
    'wav' => 'sound.png',
    'wma' => 'sound.png',
    'swf' => 'video.png',
    'avi' => 'video.png',
    'mpg' => 'video.png',
    'nb' => 'mathnb.png',
    'mws' => 'maple.png',
    'mw' => 'maple.png'
);

$CFG['GEN']['sendquestionproblemsthroughcourse'] = 1;

$CFG['GEN']['guesttempaccts'] = array(518);

$CFG['GEN']['hidedefindexmenu'] = true;
$CFG['GEN']['hideindexhelp'] = true;
$CFG['GEN']['forcecanvashttps'] = true;
$CFG['GEN']['addwww'] = false;
$CFG['GEN']['TOSpage'] = 'https://lumenlearning.com/policies/terms-of-service/';
$CFG['GET']['privacyPolicyPage'] = 'https://lumenlearning.com/policies/privacy-policy/';
$CFG['GEN']['enrollonnewinstructor'] = array(1,11);
$smallheaderlogo = '<img src="'.$imasroot.'/img/collapse.gif"/>';
$CFG['GEN']['logopad'] = '20px';

$CFG['GEN']['skipbrowsercheck'] = true;

$CFG['GEN']['meanstogetcode'] = 'requesting an instructor account on MyOpenMath.com';

$CFG['GEN']['zdapikey'] = getenv('ZENDESK_API_KEY');
$CFG['GEN']['zdurl'] = getenv('ZENDESK_API_URL');
$CFG['GEN']['zduser'] = getenv('ZENDESK_API_USER');

$questionErrorMsgToUserid = getenv('QUESTION_ERROR_MSG_USERID') ?
    getenv('QUESTION_ERROR_MSG_USERID') : 718166;
$CFG['GEN']['qerrorsendto'] = [$questionErrorMsgToUserid, 'msg',
    'Report Question Bug', true];

$CFG['coursebrowser'] = 'coursebrowserprops.js';
$CFG['coursebrowsermsg'] = 'Copy a template course';

// For OEA embedded OHM questions, show the answer & feedback (if existing)
// when questions are embedded in text.
$CFG['multiembed-showans'] = 2;

$CFG['GEN']['favicon'] = $imasroot . '/ohm/img/favicon.ico';

$CFG['GEN']['communityforumlink'] = getenv('COMMUNITY_FORUM_URL');
//$CFG['GEN']['homelinkbox'] = false;
/*$CFG['FCM'] = array(
     'SenderId' => '680665776094',
     'webApiKey' => 'AIzaSyAfFxZMM5wEUezNDaP5ZxRrXG18FPnvUHE',
     'serverApiKey' => getenv('FCM_SERVER_KEY'),
     'icon' => '/img/MOMico.png'
     );*/

$CFG['email']['new_acct_replyto'] = 'support@lumenlearning.com';
$CFG['email']['new_acct_bcclist_ohm_hook'] = array('support@lumenlearning.com');

$CFG['GEN']['useSESmail'] = true;

$CFG['hooks']['init'] = 'ohm-hooks/init.php';
$CFG['hooks']['header'] = 'ohm-hooks/header.php';
$CFG['hooks']['util/batchcreateinstr'] = '../ohm-hooks/util/batchcreateinstr.php';
$CFG['hooks']['actions'] = 'ohm-hooks/actions.php';
$CFG['hooks']['admin/actions'] = '../ohm-hooks/admin/actions.php';
$CFG['hooks']['admin/approvepending'] = '../ohm-hooks/admin/approvepending2.php';
$CFG['hooks']['admin/forms'] = '../ohm-hooks/admin/forms.php';
$CFG['hooks']['bltilaunch'] = 'ohm-hooks/bltilaunch.php';
$CFG['hooks']['validate'] = 'ohm-hooks/validate.php';
$CFG['hooks']['ltihome'] = 'ohm-hooks/ltihome.php';
$CFG['hooks']['banner'] = 'ohm-hooks/banner.php';
$CFG['hooks']['use_replica_db'] = 'ohm-hooks/use_replica_db.php';
$CFG['hooks']['util/utils'] = '../ohm-hooks/util/utils.php';
$CFG['hooks']['lti'] = __DIR__ . '/../ohm-hooks/lti13hooks.php';

// The following hooks are defined here AND in the Question API. (ohm/lumenapi/config/)
$GLOBALS['CFG']['hooks']['assess2/questions/score_engine'] = __DIR__ . '/../ohm-hooks/assess2/questions/score_engine.php';
$GLOBALS['CFG']['hooks']['assess2/questions/question_html_generator'] = __DIR__ . '/../ohm-hooks/assess2/questions/question_html_generator.php';

// The following hooks are defined in the Question API. (ohm/lumenapi/config/)
//$GLOBALS['CFG']['hooks']['assess2/assess_standalone']
//$GLOBALS['CFG']['hooks']['assess2/questions/scorepart/multiple_answer_score_part']
//$GLOBALS['CFG']['hooks']['assess2/questions/scorepart/choices_score_part']

$CFG['GEN']['footerscriptinclude'] = 'ohm-hooks/footer.php';

$CFG['desmos_calculator'] = 'https://desmos.lumenlearning.com/calculator/v1.4-all/calculator.js';

$CFG['showcalculator'] = [
    'basic' => 'Basic Calculator',
    'scientific' => 'Scientific Calculator',
    'graphing' => 'Graphing Calculator'
];

$CFG['LTI']['autoreg'] = true;

// Debug logging
$GLOBALS['ENABLE_SCORE_DEBUG'] = 'true' == getenv('ENABLE_SCORE_DEBUG');
$GLOBALS['ENABLE_LTI_DEBUG'] = 'true' == getenv('ENABLE_LTI_DEBUG');

