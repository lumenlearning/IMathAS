<?php

/*
 * This allows us to keep state and display banners only once on the same page.
 *
 * Notes / history:
 *   - https://lumenlearning.atlassian.net/browse/OHM-400
 *   - https://github.com/lumenlearning/ohm/pull/272
 */

require_once(__DIR__ . '/../ohm/services/OhmBannerService.php');
// This allows us to keep state and display banners only once per page.
$ohmBannerService = new Ohm\Services\OhmBannerService($GLOBALS['DBH'], $GLOBALS['userid'], 0);

/**
 * Display a banner specific to teachers and/or users.
 *
 * @param int $userRights The user's rights from imas_users.
 */
function displayBanners(int $userRights): void
{
    global $ohmBannerService; // This allows us to keep state and display banners only once per page.
    $ohmBannerService->setUserRights($userRights);
    $ohmBannerService->setDisplayOnlyOncePerBanner(true);

    $ohmBannerService->showTeacherBannersForTeachersOnly();
    $ohmBannerService->showStudentBannersForStudentsOnly();
}
