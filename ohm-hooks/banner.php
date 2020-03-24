<?php

/*
 * This allows us to keep state and display banners only once on the same page.
 *
 * Notes / history:
 *   - https://lumenlearning.atlassian.net/browse/OHM-400
 *   - https://github.com/lumenlearning/ohm/pull/272
 */

use OHM\Models\NoticeDismissal;

require_once(__DIR__ . '/../ohm/models/NoticeDismissal.php');
require_once(__DIR__ . '/../ohm/includes/OhmBanner.php');
$ohmBanner = new Ohm\Includes\OhmBanner(0);
$ohmBanner->setDisplayOnlyOncePerBanner(true);

/**
 * Display a banner specific to teachers and/or users.
 *
 * @param int $userRights The user's rights from imas_users.
 */
function displayBanner(int $userRights): void
{
    $userId = $GLOBALS['userid'];
    // TODO: Banners should have unique identifiers at some point in the future.
    //       This should probably be passed as a function argument.
    $bannerId = 1;

    // Determine if the user has already dismissed this banner.
    $noticeDismissal = new NoticeDismissal($GLOBALS['DBH']);
    $noticeDismissal->findByUserIdAndNoticeId($userId, $bannerId);
    if (!is_null($noticeDismissal->getDismissedAt())) {
        return;
    }

    global $ohmBanner; // This allows us to keep state and display banners only once.
    $ohmBanner->setUserRights($userRights);

    $ohmBanner->showTeacherBannerForTeachersOnly();
    $ohmBanner->showStudentBannerForStudentsOnly();
}
