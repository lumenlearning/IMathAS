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

//aws config
$AWSbucket = "development" == $configEnvironment ? null : $_ENV['S3_MAIN_BUCKET_NAME'];
$dbname = 'myopenmathdb';

//login prompts
$loginprompt = "Username";
$longloginprompt = "Enter a username.  Use only numbers, letters, or the _ character.";
$loginformat = '/^[\w\.@\-]+$/';

//require email confirmation of new users?
$emailconfirmation = false;

//email to send notices from
$sendfrom = "do-not-reply@lumenlearning.com";
$accountapproval = "support@lumenlearning.com";

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
$GLOBALS['student_pay_api']['debug'] = false;

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
$CFG['CPS']['theme'] = array("lumen.css_fw1920",1);
$CFG['CPS']['themelist'] ="lumen.css";
$CFG['CPS']['themenames'] = "Lumen Theme";

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
    'drill'=>'assess_tiny.png',
    'inline'=>'inline_tiny.png',
    'linked'=>'html_tiny.png',
    'forum'=>'forum_tiny.png',
    'wiki'=>'wiki_tiny.png',
    'folder'=>'folder_tiny.png',
    'calendar'=>'1day.png');

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

$CFG['coursebrowser'] = 'coursebrowserprops.js';
$CFG['coursebrowsermsg'] = 'Start with a template course';

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

$CFG['OHM']['new_instructor_approval_reply_to'] = 'support@lumenlearning.com';
$CFG['OHM']['new_instructor_approval_non_customer_bcc_list'] = array('paul@lumenlearning.com');

$CFG['GEN']['useSESmail'] = true;
function ohmSESmail($email, $from, $subject, $message, $replyto='', $bccList=array()) {
	require_once(__DIR__ . "/../includes/mailses.php");
	$ses = new SimpleEmailService(getenv('SES_KEY_ID'), getenv('SES_SECRET_KEY'), 'email.us-west-2.amazonaws.com');

	$m = new SimpleEmailServiceMessage();
	$m->addTo($email);
	foreach($bccList as $address) {
		$m->addBCC($address);
	}
	$m->setFrom($from);
	if ($replyto != '') {
		$m->addReplyTo($replyto);
	}
	$m->setSubject($subject);
	$m->setMessageFromString(null,$message);
	$ses->sendEmail($m);
}

?>
