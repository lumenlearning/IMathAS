<?php

/*
 * This allows us to keep state and display banners only once on the same page.
 *
 * To set which banner is displayed to users, modify the banner ID where
 * the displayBanner() function is being called. As of 2020 Mar 24, look
 * in /index.php.
 *
 * Notes / history:
 *   - https://lumenlearning.atlassian.net/browse/OHM-400
 *   - https://github.com/lumenlearning/ohm/pull/272
 */

use OHM\Models\NoticeDismissal;

require_once(__DIR__ . '/../ohm/models/NoticeDismissal.php');
require_once(__DIR__ . '/../ohm/includes/OhmBanner.php');
// This allows us to keep state and display banners only once per page.
$ohmBanner = new Ohm\Includes\OhmBanner(0, 0);
$ohmBanner->setDisplayOnlyOncePerBanner(true);

/**
 * Display a banner specific to teachers and/or users.
 *
 * @param int $userRights The user's rights from imas_users.
 * @param int $bannerId The banner ID to display.
 */
function displayBanner(int $userRights, int $bannerId): void
{
    // Determine if the user has already dismissed this banner.
    $noticeDismissal = new NoticeDismissal($GLOBALS['DBH']);
    $noticeDismissal->findByUserIdAndNoticeId($GLOBALS['userid'], $bannerId);
    if (!is_null($noticeDismissal->getDismissedAt())) {
        return;
    }

    global $ohmBanner; // This allows us to keep state and display banners only once per page.
    $ohmBanner->setUserRights($userRights);
    $ohmBanner->setBannerId($bannerId);

    $ohmBanner->showTeacherBannerForTeachersOnly();
    $ohmBanner->showStudentBannerForStudentsOnly();
}
