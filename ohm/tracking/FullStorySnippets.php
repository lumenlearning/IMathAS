<?php

namespace OHM\Tracking;

use PDO;

class FullStorySnippets
{
    public const EXCLUDE_COURSES = [
        'Course Page' // OHM reports this when on the course listing page.
    ];

    /**
     * Get the HTML snippet required to load FullStory during a user's
     * session.
     *
     * This only returns HTML elements as a string. It does not output
     * anything to the user.
     *
     * This string should be inserted into the <head> section of a web page.
     *
     * @return string An HTML snippet required to load FullStory on a page.
     */
    public static function getHeaderSnippet(): string
    {
        global $basesiteurl;

        return sprintf(
            '<script src="%s/ohm/js/fullstory.js"></script>' . "\n",
            $basesiteurl);
    }

    /**
     * Get the HTML snippet that enables marking up PII for
     * dev / QA / debugging purposes.
     *
     * This assists QA & devs to ensure sensitive user information s properly
     * excluded or masked during non-production testing.
     *
     * @return string An HTML snippet that enables marking up PII.
     */
    public static function getDebugMarkupSnippet(): string
    {
        global $basesiteurl;

        return sprintf(
            '<link rel="stylesheet" href="%s/ohm/tracking/sensitive_info_highlight.css">' . "\n",
            $basesiteurl);
    }

    /**
     * Output a JS snippet to identify the current user to FullStory.
     *
     * @return string The JS snippet to identify the user.
     *                Empty for unauthenticated users.
     */
    public static function getCurrentUserIdentitySnippet(): string
    {
        global $userid;

        if (empty($userid) || 1 > $userid) {
            return '';
        }

        $realUserId = FullStoryIdentity::getRealUserId();
        return sprintf('<script type="text/javascript">
  if ("undefined" !== FS && null != FS) {
    FS.identify("OHM-%s");
  }
</script>' . "\n", $realUserId);
    }

    /**
     * Get the HTML that provides user metadata to FullStory.
     *
     * Notes:
     * - Some metadata is only available after some of the page has loaded.
     * - To ensure all metadata is available for FullStory, call this
     *   method in footer.php.
     *
     * @return string An HTML snippet that provides user metadata to FullStory.
     *                An empty string for unauthenticated users.
     */
    public static function getCurrentUserMetadataSnippet(): string
    {
        global $myrights;

        // We can only provide user metadata for authenticated users.
        if (empty($myrights) || 0 == $myrights) {
            return '';
        }

        $metadataAsString = json_encode(self::generateLoggedInUserMetadata());

        return sprintf('<script type="text/javascript">
  if ("undefined" !== FS && null != FS) {
    FS.setUserVars(%s);
  }
</script>' . "\n", $metadataAsString);
    }

    /**
     * Generate user metadata. If a user is being impersonated, the originally
     * logged in user's metadata will be returned instead of the impersonated
     * user.
     *
     * @return array The user's metadata.
     *               An empty array for unauthenticated users.
     */
    public static function generateLoggedInUserMetadata(): array
    {
        $impostorUserId = FullStoryIdentity::getImpostorUserId();

        return empty($impostorUserId) // User is not impersonated.
            ? self::generateUserMetadataByGlobals()
            : self::generateUserMetadataByUserId($impostorUserId);
    }

    /**
     * Generate the metadata we'll send to FullStory for the current user.
     *
     * Use: When the current user is NOT impersonating another user.
     *
     * - This method relies on data obtained from global variables.
     * - Do not use for impersonated users.
     *
     * @return array The user's metadata.
     *               An empty array for unauthenticated or impersonated users.
     */
    public static function generateUserMetadataByGlobals(): array
    {
        global $myrights, $userid, $groupid, $cid, $coursename,
               $ohmCourseTeacherId, $ohmEnrollmentId;

        // We can only provide user metadata for authenticated users.
        if (empty($myrights) || 0 == $myrights) {
            return [];
        }

        // Don't return anything if a user is currently being impersonated.
        if (FullStoryIdentity::getImpostorUserId()) {
            return [];
        }

        $metadata = [
            "product_str" => 'ohm',
            "userId_str" => 'OHM-' . $userid, // always available with $myrights.
            "role_str" => FullStoryIdentity::getUserRole(),
        ];

        // The following become available later during page loads, after the
        // header has been displayed.
        if (!empty($groupid)) {
            $metadata['groupId_int'] = intval($groupid);
        }

        if (!empty($cid)) {
            $metadata['courseId_int'] = intval($cid);
        }

        if (!empty($coursename) && !in_array($coursename, self::EXCLUDE_COURSES)) {
            $metadata['courseName_str'] = $coursename;
        }

        if (!empty($ohmCourseTeacherId)) {
            $metadata['instructorId_str'] = 'OHM-' . $ohmCourseTeacherId;
        }

        if (!empty($ohmEnrollmentId)) {
            $metadata['enrollmentId_str'] = 'OHM-' . $ohmEnrollmentId;
        }

        return $metadata;
    }

    /**
     * Generate the metadata we'll send to FullStory for a specific user.
     *
     * Use: When the current user is impersonating another user.
     *
     * This method does not use global variables to get user data and is
     * most useful when a user is impersonating another user.
     *
     * @param int $userId The user ID to return metadata for.
     * @return array The user's metadata.
     *               An empty array for unauthenticated users or invalid user IDs.
     */
    public static function generateUserMetadataByUserId(int $userId): array
    {
        global $DBH, $cid, $coursename, $ohmCourseTeacherId;

        $metadata = [
            "product_str" => 'ohm',
            "userId_str" => 'OHM-' . $userId,
        ];

        $stm = $DBH->prepare("SELECT rights, groupid FROM imas_users WHERE id=:userId");
        $stm->execute([':userId' => $userId]);
        $user = $stm->fetch(PDO::FETCH_ASSOC);

        $metadata['role_str'] = FullStoryIdentity::getUserRole($user['rights']);
        $metadata['groupId_int'] = $user['groupid'];

        if (!empty($cid)) {
            $metadata['courseId_int'] = intval($cid);
        }

        if (!empty($coursename) && !in_array($coursename, self::EXCLUDE_COURSES)) {
            $metadata['courseName_str'] = $coursename;
        }

        if (!empty($ohmCourseTeacherId)) {
            $metadata['instructorId_str'] = 'OHM-' . $ohmCourseTeacherId;
        }

        /*
         * Excluding enrollment ID because an impostor is very likely to be
         * a teacher impersonating their own student, and they are not "enrolled"
         * in their own course.
         */

        return $metadata;
    }
}
