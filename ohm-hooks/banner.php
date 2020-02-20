<?php
/**
 * Display a banner specififc to teachers and/or users.
 *
 * @param int $userRights The user's rights from imas_users.
 */
function displayBanner(int $userRights): void
{
    require_once(__DIR__ . '/../ohm/includes/OhmBanner.php');

    $ohmBanner = new Ohm\Includes\OhmBanner($userRights);
    $ohmBanner->showTeacherBannerForTeachersOnly();
    $ohmBanner->showStudentBannerForStudentsOnly();
}
