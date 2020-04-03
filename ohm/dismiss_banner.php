<?php
/**
 * This is used by /ohm/views/banner/banner.js to permanently dismiss banners
 * displayed on the home and course pages.
 */

use OHM\Models\BannerDismissal;

require_once(__DIR__ . '/../init.php');

$userId = $GLOBALS['userid'];
$bannerId = intval($_POST['banner-id']);

if (empty($userId)) {
    http_response_code(401);
    exit;
}

if (empty($bannerId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing banner ID.']);
    exit;
}

$bannerDismissal = new BannerDismissal($GLOBALS['DBH']);
$found = $bannerDismissal->findByUserIdAndBannerId($userId, $bannerId);
if (!$found) {
    $bannerDismissal->setUserId($userId);
    $bannerDismissal->setBannerId($bannerId);
}
$bannerDismissal->dismissBannerNow();
