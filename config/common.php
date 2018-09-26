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

//do safe course delete
$CFG['GEN']['doSafeCourseDelete'] = true;

$CFG['GEN']['pandocserver'] = '54.191.55.159';//'54.212.251.50';
$CFG['GEN']['livepollserver'] = 'livepoll.myopenmath.com';
$CFG['GEN']['livepollpassword'] = 'testing';

//force use of better hashed pw
$CFG['GEN']['newpasswords'] = "only";

//hide Email button on Roster and GB pages
$CFG['GEN']['noEmailButton'] = true;

$CFG['use_csrfp'] = 'log';

//Amazon's load balancer acts as proxy. Put the real IP address in REMOTE_ADDR
//for storing as user's IP address
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
  $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

$CFG['cleanup']['authcode'] = getenv('SES_KEY_ID');

//database access settings
$AWSkey = getenv('AWS_ACCESS_KEY_ID');
$AWSsecret = getenv('AWS_SECRET_KEY');
$dbserver = getenv('DB_SERVER');
$dbusername = getenv('DB_USERNAME');
$dbpassword = getenv('DB_PASSWORD');
if (getenv('imasroot')!==false) {
  $imasroot = getenv('imasroot');
}

