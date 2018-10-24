<?php
$email_subject = Sanitize::encodeStringForDisplay($_POST['subject']);
$message = Sanitize::encodeStringForDisplay($_POST['message']);
$firstname = Sanitize::encodeStringForDisplay($_POST['firstname']);
$lastname = Sanitize::encodeStringForDisplay($_POST['lastname']);
$email = Sanitize::encodeStringForDisplay($_POST['email']);
$id = Sanitize::encodeStringForDisplay($_POST['id']);

$email_from = 'support@lumenlearning.com';
$email_body = "The following message was submitted via the contact form: \n $message. \n\n Sender name: $firstname $lastname \n Sender email: $email \n\ Sender ID: $id";

require_once("../includes/email.php");
send_email($to, $email_from, $email_subject, $email_body,
	$email, null, 10);

header('Location: lumenhelp.php');
exit();

