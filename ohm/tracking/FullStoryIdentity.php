<?php

namespace OHM\Tracking;

class FullStoryIdentity
{
    /**
     * Get the user's logged in ID. This works even if a user is impersonating
     * another user.
     *
     * @return int|null The logged in user's ID. Null for unauthenticated users.
     */
    public static function getRealUserId(): ?int
    {
        global $userid;

        if (empty($userid) || 1 > $userid) {
            return null;
        }

        $impostorUserId = self::getImpostorUserId();
        return empty($impostorUserId) ? $userid : $impostorUserId;
    }

    /**
     * Get an impostor's user ID.
     *
     * Used to determine the originally logged in user's ID when they are
     * impersonating (emulating) another user.
     *
     * @return int|null The originally logged in user's ID.
     *                  Null if the current user is not impersonated.
     */
    public static function getImpostorUserId(): ?int
    {
        if (!empty($_SESSION['emulateuseroriginaluser'])
            && 0 < intval($_SESSION['emulateuseroriginaluser'])) {
            return intval($_SESSION['emulateuseroriginaluser']);
        }
        return null;
    }

    /**
     * Determine if we need to identify the user to FullStory. This will only
     * happen once per individual user session.
     *
     * @return bool
     */
    public static function userNeedsFullStoryIdentity(): bool
    {
        $realUserId = self::getRealUserId();

        // Prevent using FS.identify during first LTI launch request. OHM
        // immediately submits a form to load another page and FS does not
        // have enough time to reliably run FS.identify.
        if (isset($_GET['accessibility'])) {
            return false;
        }

        return (
            empty($_SESSION)
            || empty($_SESSION['sent-fullstory-user-identity'])
            || $realUserId != $_SESSION['sent-fullstory-user-identity']
        );
    }

    /**
     * Get a user's role as a string. (10 = "student", etc)
     *
     * @param int|null $specifiedRights The rights value to use instead of $GLOBALS['myrights'].
     *                                  Pass null or omit entirely to use $GLOBALS['myrights'].
     * @return string A user's role name. "none" for unauthenticated users.
     */
    public static function getUserRole(?int $specifiedRights = null): string
    {
        global $myrights;

        $rightsToCheck = is_null($specifiedRights) ? $myrights : $specifiedRights;

        if (empty($rightsToCheck)) {
            return 'none';
        }

        $roleName = 'unknown';
        switch ($rightsToCheck) {
            case 5:
                $roleName = 'guest';
                break;
            case 10:
                $roleName = 'student';
                break;
            case 12:
                $roleName = 'pending-approval';
                break;
            case 15:
                $roleName = 'tutor';
                break;
            case 20:
                $roleName = 'instructor';
                break;
            case 40:
                $roleName = 'limited-course-creator';
                break;
            case 75:
                $roleName = 'group-admin';
                break;
            case 100:
                $roleName = 'administrator';
                break;
        }

        return $roleName;
    }
}
