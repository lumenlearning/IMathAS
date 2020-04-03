<?php

/*
 * This allows us to keep state and display banners only once on the same page.
 *
 * To set which banner is displayed to users, modify the banner ID where
 * the displayBanner() function is being called.
 * As of 2020 Mar 24, look in:
 *   - /index.php
 *   - /course/course.php
 *
 * Notes / history:
 *   - https://lumenlearning.atlassian.net/browse/OHM-400
 *   - https://github.com/lumenlearning/ohm/pull/272
 */

use OHM\Models\BannerDismissal;

require_once(__DIR__ . '/../ohm/models/BannerDismissal.php');
require_once(__DIR__ . '/../ohm/services/OhmBannerService.php');
// This allows us to keep state and display banners only once per page.
$ohmBannerService = new Ohm\Services\OhmBannerService(0, 0);
$ohmBannerService->setDisplayOnlyOncePerBanner(true);

/**
 * Display a banner specific to teachers and/or users.
 *
 * @param int $userRights The user's rights from imas_users.
 * @param int $bannerId The banner ID to display.
 */
function displayBanner(int $userRights, int $bannerId): void
{
    // Determine if the user has already dismissed this banner.
    $bannerDismissal = new BannerDismissal($GLOBALS['DBH']);
    $bannerDismissal->findByUserIdAndBannerId($GLOBALS['userid'], $bannerId);
    if (!is_null($bannerDismissal->getDismissedAt())) {
        return;
    }

    global $ohmBannerService; // This allows us to keep state and display banners only once per page.
    $ohmBannerService->setUserRights($userRights);
    $ohmBannerService->setBannerId($bannerId);

    $ohmBannerService->showTeacherBannerForTeachersOnly();
    $ohmBannerService->showStudentBannerForStudentsOnly();
}
