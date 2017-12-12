<?php
header('P3P: CP="ALL CUR ADM OUR"');
$init_skip_csrfp = true;
include("../../init_without_validate.php");
unset($init_skip_csrfp);

echo '<link rel="stylesheet" type="text/css" href="' . $imasroot . '/themes/lumen.css" />';

if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
  $urlmode = 'https://';
} else {
  $urlmode = 'http://';
}
if ($enablebasiclti != true) {
  echo "LTI not enabled";
  exit;
}

function reporterror($err) {
  global $imasroot;
  printf('<p>%s</p>', Sanitize::encodeStringForDisplay($err));
  exit;
}

$errors = [];


//verify necessary POST values for LTI.  OAuth specific will be checked later
if (empty($_REQUEST['lti_version'])) {
  reporterror("Insufficient launch information. This might indicate your browser is set to restrict third-party cookies. Check your browser settings and try again.");
}

//check OAuth Signature!
require_once '../../includes/OAuth.php';
require_once '../../includes/ltioauthstore.php';

//set up OAuth
$store = new IMathASLTIOAuthDataStore();
$server = new OAuthServer($store);
$method = new OAuthSignatureMethod_HMAC_SHA1();
$server->add_signature_method($method);
$request = OAuthRequest::from_request();
$base = $request->get_signature_base_string();
try {
  $requestinfo = $server->verify_request($request);
} catch (Exception $e) {
//  reporterror($e->getMessage());
  array_push($errors, $e->getMessage());
}
$store->mark_nonce_used($request);

// Check for other required LTI parameters
if (empty($_REQUEST['user_id'])) {
  array_push($errors, "User information not provided (LTI parameter 'user_id' is required)");
}
if (empty($_REQUEST['context_id'])) {
  array_push($errors, "Course information not provided (LTI parameter 'context_id' is required)");
}
if (empty($_REQUEST['roles'])) {
  array_push($errors, "Role of user not provided (LTI parameter 'roles' is required)");
}
if (empty($_REQUEST['oauth_consumer_key'])) {
  array_push($errors, "No 'oauth_consumer_key' was sent");
}

// Display the success message or list the errors
// todo: pretty styling

echo '<div id="ohm-integration-message">
        <div id="logo"><img src="' . $imasroot . '/ohm/img/ohm-logo-color-400.png" alt="lumen learning online homework manager logo" /></div>
        <div id="text">';

if(empty($errors)){
  echo '<h1>OHM Integration Successful!</h1>
        <p>Please open the final content item, Complete Test, to finish this test process.</p>';
} else {

  echo '<h1>It looks like we\'ve encountered some errors:</h1>';
  echo '<ul>';

  foreach($errors as $val) {
    printf('<li>%s</li>', Sanitize::encodeStringForDisplay($val));
  }

  echo '</ul>';
  echo '<p style="margin-top: 35px;">
            Please take a screenshot of this page and send us an email at <a href="mailto:support@lumenlearning.com">support@lumenlearning.com</a>, letting us know
            you\'re having trouble setting your Lumen LTI connection, and which institution you are from. Someone from
            our support team will then get in touch to help troubleshoot these problems.
        </p>';
}

echo '</div></div>';

?>
