<?php

// Can be 'development, 'staging', 'production'
$configEnvironment = empty(getenv('CONFIG_ENV')) ? 'development' : getenv('CONFIG_ENV');

// All common config options are here
// The initial database config options are here as well
require(__DIR__.'/config/common.php');


// *** CHOOSE THE APPROPRIATE CONFIG FILE FOR THE DOMAIN/CONFIG_ENV
if ($configEnvironment == 'development') {
  // You may want to include the appropriate prod config file inside your local.php
  // If you wanted OHM config for example, you should `require("ohm.php");`
  require(__DIR__.'/config/local.php');

} else if (strpos($_SERVER['HTTP_HOST'], 'wamap.org') !== false) {

  require(__DIR__.'/config/wamap.php');

} else if (strpos($_SERVER['HTTP_HOST'], 'lumenlearning.com') !== false) {

  if (file_exists(__DIR__ . '/ohm/maintenance_active')) {
    $maintenance_text = file_get_contents(__DIR__ . '/ohm/maintenance_active');
    require(__DIR__ . '/ohm/maintenance.php');
    exit;
  }

  require(__DIR__.'/config/ohm.php');

} else {
  //production beanstalk now requires the CONFIG_ENV to be set.

  require(__DIR__.'/config/myopenmath.php');

}

if ($configEnvironment == 'development' || $configEnvironment == 'staging') {
  enableDisplayErrors();
}

//base site url - use when generating full URLs to site pages.
$httpmode = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO']=='https')
    ? 'https://' : 'http://';
$GLOBALS['basesiteurl'] = $httpmode . Sanitize::domainNameWithPort($_SERVER['HTTP_HOST']) . $imasroot;



// *** COMMON CONFIG AND DB CONNECTION SETUP

//session path
if (strpos($_SERVER['HTTP_HOST'],'localhost')===false) {
  $sessionpath = "/tmp";
}

ini_set("upload_max_filesize", "10485760");
ini_set("post_max_size", "10485760");

$CFG['GEN']['useSESmail'] = true;
function SESmail($email,$from,$subject,$message,$replyto='') {
  require_once("includes/mailses.php");
  $ses = new SimpleEmailService(getenv('SES_KEY_ID'), getenv('SES_SECRET_KEY'), 'email.us-west-2.amazonaws.com');

  $m = new SimpleEmailServiceMessage();
  $m->addTo($email);
  $m->setFrom($from);
  if ($replyto != '') {
    $m->addReplyTo($replyto);
  }
  $m->setSubject($subject);
  $m->setMessageFromString(null,$message);
  $ses->sendEmail($m);
}

function enableDisplayErrors() {
  ini_set('display_errors',1);
  error_reporting(E_ERROR | E_USER_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_PARSE);
}


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
try {
  $DBH = new PDO("mysql:host=$dbserver;dbname=$dbname", $dbusername, $dbpassword);
  //$DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT );
  //loud during beta
  $DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  // global $DBH;
  $GLOBALS["DBH"] = $DBH;
} catch(PDOException $e) {
  die("<p>Could not connect to database: <b>" . $e->getMessage() . "</b></p></div></body></html>");
}
$DBH->query("set session sql_mode=''");
/*


$link = mysql_connect($dbserver,$dbusername, $dbpassword)
or die("<p>Could not connect : " . mysql_error() . "</p></div></body></html>");
mysql_select_db($dbname)
or die("<p>Could not select database</p></div></body></html>");
function addslashes_deep($value) {
return (is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value));
}
if (!get_magic_quotes_gpc()) {
 $_GET    = array_map('addslashes_deep', $_GET);
 $_POST  = array_map('addslashes_deep', $_POST);
 $_COOKIE = array_map('addslashes_deep', $_COOKIE);
}
mysql_query("set session sql_mode=''");
*/
unset($dbserver);
unset($dbusername);
unset($dbpassword);
