<?php

namespace OHM\Tracking;

/**
 * This class loads FullStory and provides user metadata to FullStory.
 */
class FullStory
{

    const EXCLUDE_COURSES = [
        'Course Page' // OHM reports this when on the course listing page.
    ];

    /**
     * Output the JS snippet required to load FullStory during a user's
     * session.
     *
     * @return bool True if the snippet was output. False if not.
     */
    public static function outputHeaderSnippet(): bool
    {
        if (!self::isFullStoryEnabled()) {
            return false;
        }

        // FullStory JS snippet.
        echo self::getHeaderSnippet();

        // User metadata for FullStory.
        echo self::getUserMetadataSnippet();

        // For dev / QA / debugging.
        if (self::isDebugMarkupEnabled()) {
            echo self::getDebugMarkupSnippet();
        }

        return true;
    }

    /**
     * Determine if FullStory is enabled in OHM.
     *
     * @return bool True if enabled. False if not.
     */
    public static function isFullStoryEnabled(): bool
    {
        return 'true' == getenv('FULLSTORY_ENABLED');
    }

    /**
     * Determine if PII should be marked with visual indicators for
     * dev / debugging purposes.
     *
     * Current requirements:
     *   - The environment variable `CONFIG_ENV` is NOT `production`.
     *   - The currently logged in user is an administrator. (Rights == 100)
     *
     * @return bool True if debug markup should be displayed. False if not.
     */
    public static function isDebugMarkupEnabled(): bool
    {
        global $myrights, $configEnvironment;

        return 100 == $myrights && 'production' != $configEnvironment;
    }

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
    public static function getUserMetadataSnippet(): string
    {
        global $myrights;

        // We can only provide user metadata for authenticated users.
        if (empty($myrights) || 0 == $myrights) {
            return '';
        }

        $metadataAsString = json_encode(self::generateUserMetadata());

        return sprintf('<script type="text/javascript">
  if ("undefined" !== FS && null != FS) {
    FS.setUserVars(%s);
  }
</script>' . "\n", $metadataAsString);
    }

    /**
     * Generate the metadata we'll send to FullStory for the current user.
     *
     * @return array The user's metadata. An empty array for unauthenticated users.
     */
    public static function generateUserMetadata(): array
    {
        global $myrights, $userid, $groupid, $cid, $coursename,
               $ohmCourseTeacherId, $ohmEnrollmentId;

        // We can only provide user metadata for authenticated users.
        if (empty($myrights) || 0 == $myrights) {
            return [];
        }

        $metadata = [
            "product_str" => 'ohm',
            "userId_str" => 'OHM-' . $userid, // always available with $myrights.
            "role_str" => self::getUserRole(),
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
     * Get a user's role as a string. (10 = "student", etc)
     *
     * @return string A user's role name. "none" for unauthenticated users.
     */
    public static function getUserRole(): string
    {
        global $myrights;

        if (empty($myrights) || 0 == $myrights) {
            return 'none';
        }

        $roleName = 'unknown';
        switch ($myrights) {
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
