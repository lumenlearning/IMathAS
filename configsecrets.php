<?php
//MOM account secrets
$AWSkey = getenv('AWS_ACCESS_KEY_ID');
$AWSsecret = getenv('AWS_SECRET_KEY');
$AWSbucket = getenv('PARAM1');
$dbserver = getenv('PARAM2');
$dbname = getenv('PARAM3');
$dbusername = getenv('PARAM4');
$dbpassword = getenv('PARAM5');
if (getenv('imasroot')!==false) {
	$imasroot = getenv('imasroot');
}
?>
