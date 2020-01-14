<?php

require("../init.php");
require_once("../includes/filehandler.php");


$imageRawData = $_POST['imageData'];
if (empty($imageRawData)) {
    http_response_code(400);
    echo '{"error": "No image data provided."}';
    exit;
}

/*
 * Get the image data
 */
$imageFormatMatcher = preg_match('/image\/(\w+);/', $imageRawData, $imageFormatMatches);
$imageFormat = $imageFormatMatches[1];

$imageDataIdx = strpos($imageRawData, 'base64,') + 7;
$imageData = base64_decode(substr($imageRawData, $imageDataIdx));

/*
 * Store the image on S3.
 */
$filepathname = sprintf('desmos-images/%s/%s.%s', $userid,
    uniqid('', true), $imageFormat);
storecontenttofile($imageData, $filepathname, $sec = "public");

/*
 * Respond with a URL to the file.
 */
http_response_code(201);
echo json_encode([
    'imageUrl' => sprintf('%s%s.s3.amazonaws.com/%s',
        $GLOBALS['urlmode'], $GLOBALS['AWSbucket'], $filepathname),
]);
