<?php
/**
 * This is used by /ohm/views/banner/banner.js to permanently dismiss banners
 * displayed on the home and course pages.
 */

use OHM\Models\NoticeDismissal;

require_once(__DIR__ . '/../init.php');

$userId = $GLOBALS['userid'];
$noticeId = intval($_POST['notice-id']);

if (empty($userId)) {
    http_response_code(401);
    exit;
}

if (empty($noticeId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing notice ID.']);
    exit;
}

$noticeDismissal = new NoticeDismissal($GLOBALS['DBH']);
$found = $noticeDismissal->findByUserIdAndNoticeId($userId, $noticeId);
if (!$found) {
    $noticeDismissal->setUserId($userId);
    $noticeDismissal->setNoticeId($noticeId);
}
$noticeDismissal->dismissNoticeNow();
