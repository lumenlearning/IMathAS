<?php

namespace OHM\Tracking;

/**
 * This class loads FullStory and provides user metadata to FullStory.
 */
class FullStory
{

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

        $roleName = self::getUserRole();

        return sprintf('<script type="text/javascript">
  if ("undefined" !== FS && null != FS) {
    FS.setUserVars({
     "role_str" : "%s",
    });
  }
</script>' . "\n",
            $roleName);
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
