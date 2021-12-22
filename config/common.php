<?php

/*** common config ***/
if (PHP_INT_SIZE==8) {
  $mathimgurl = "/cgi-bin/mimetex64.cgi";
} else {
  $mathimgurl = "/cgi-bin/mimetex.cgi";
}
$imasroot = "";
$allowmacroinstall = false;
$CFG['GEN']['AWSforcoursefiles'] = true;
$CFG['static_server'] = 'https://static.myopenscience.com';

//min 8 char password
$CFG['acct']['passwordMinlength'] = 8;

//do safe course delete
$CFG['GEN']['doSafeCourseDelete'] = true;

$CFG['GEN']['pandocserver'] = 'https://livepoll.myopenmath.com';
$CFG['GEN']['livepollserver'] = 'livepoll.myopenmath.com';
$CFG['GEN']['livepollpassword'] = 'testing';

//force use of better hashed pw
$CFG['GEN']['newpasswords'] = "only";

//hide Email button on Roster and GB pages
$CFG['GEN']['noEmailButton'] = true;

// rate limit page access
$CFG['GEN']['ratelimit'] = 0.1;

// set email handler
$CFG['GEN']['useSESmail'] = true;
$CFG['email']['handlerpriority'] = 0;

$CFG['customtypes'] = ['desmos' => 'Custom1'];

// temporary, for testing impact on IOPS
$use_local_sessions = true;

//$CFG['use_csrfp'] = 'log';

//log LTI updates
//$CFG['LTI']['logupdate'] = true;

//Amazon's load balancer acts as proxy. Put the real IP address in REMOTE_ADDR
//for storing as user's IP address
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
  $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

$CFG['cleanup']['authcode'] = getenv('SES_KEY_ID');
$CFG['email']['authcode'] = getenv('SES_KEY_ID');
//$CFG['cleanup']['delay'] = 30;

$CFG['LTI']['authcode'] = getenv('SES_KEY_ID');
$CFG['LTI']['logltiqueue'] = true;
$CFG['LTI']['usequeue'] = true;

$CFG['hooks']['admin/forms'] = "myopenmath/hooks.php";
$CFG['hooks']['admin/actions'] = "myopenmath/hooks.php";
$CFG['hooks']['admin/approvepending'] = "myopenmath/hooks.php";

//database access settings
require(__DIR__ . '/database.php');

$AWSkey = getenv('AWS_ACCESS_KEY_ID');
$AWSsecret = getenv('AWS_SECRET_KEY');
if (getenv('imasroot')!==false) {
  $imasroot = getenv('imasroot');
}

