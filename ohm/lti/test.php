<?php
header('P3P: CP="ALL CUR ADM OUR"');
$init_skip_csrfp = true;
include("../../init_without_validate.php");
unset($init_skip_csrfp);


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
  reporterror("Insufficient launch information. This might indicate your browser is set to restrict third-party cookies. Check your browser settings and try again");
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
  array_push($errors, "Role of user not provided (LTI parameter 'roles' is required");
}
if (empty($_REQUEST['oauth_consumer_key'])) {
  array_push($errors, "No 'oauth_consumer_key' was sent");
}

// Display the success message or list the errors
// todo: pretty styling
if(empty($errors)){
  echo "<h1>Everything looks great!</h1>";
} else{

  echo "<h1>There were errors</h1>";
  echo "<ul>";

  foreach($errors as $val) {
    printf('<li>%s</li>', Sanitize::encodeStringForDisplay($val));
  }

  echo "</ul>";
}

?>
