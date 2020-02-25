<?php

/*
 * This allows us to keep state and display banners only once.
 *
 * Notes / history:
 *   - https://lumenlearning.atlassian.net/browse/OHM-400
 *   - https://github.com/lumenlearning/ohm/pull/272
 */
require_once(__DIR__ . '/../ohm/includes/OhmBanner.php');
$ohmBanner = new Ohm\Includes\OhmBanner(0);
$ohmBanner->setDisplayOnlyOncePerBanner(true);

/**
 * Display a banner specififc to teachers and/or users.
 *
 * @param int $userRights The user's rights from imas_users.
 */
function displayBanner(int $userRights): void
{
    // This allows us to keep state and display banners only once.
    global $ohmBanner;
    $ohmBanner->setUserRights($userRights);

    $ohmBanner->showTeacherBannerForTeachersOnly();
    $ohmBanner->showStudentBannerForStudentsOnly();
}
