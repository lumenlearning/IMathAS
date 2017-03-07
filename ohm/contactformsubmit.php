<?php
$email_subject = $_POST['subject'];
$message = $_POST['message'];
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$email = $_POST['email'];
$id = $_POST['id'];


$email_from = 'support@lumenlearning.com';
$email_body = "The following message was submitted via the contact form: \n $message. \n\n Sender name: $firstname $lastname \n Sender email: $email \n\ Sender ID: $id";

$to = "support@lumenlearning.com";
$headers = "From: $email_from \r\n";
$headers .= "Reply-To: $email \r\n";
mail($to,$email_subject,$email_body,$headers);
header('Location: lumenhelp.php');
exit();
?>
