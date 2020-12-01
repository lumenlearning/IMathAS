<?php

require('../init_without_validate.php');

$message = '<p>New partner signup!</p>';
$message .= '<p>School: '.Sanitize::encodeStringForDisplay($_POST['schoolname']).'</p>';
$message .= '<p>Faculty: '.Sanitize::encodeStringForDisplay($_POST['faculty']).'</p>';
$message .= '<p>Admin: '.Sanitize::encodeStringForDisplay($_POST['admin']).'</p>';
$message .= '<p>Stu count: '.Sanitize::encodeStringForDisplay($_POST['stus']).'</p>';
$message .= '<p>Type: '.Sanitize::encodeStringForDisplay($_POST['memtype']).'</p>';
$message .= '<p>Details:</p><pre>';
$message .= Sanitize::encodeStringForDisplay(print_r(json_decode($_POST['details'],true), true));
$message .= '</pre>';

require_once("../includes/email.php");
send_email('sales@myopenmath.com', $sendfrom, 'New Partner Signup', $message, array(), array(), 10);

echo 'OK';