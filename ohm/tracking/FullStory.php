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
        echo FullStorySnippets::getHeaderSnippet();

        // Identify the user to FullStory only once per session.
        if (FullStoryIdentity::userNeedsFullStoryIdentity()) {
            echo FullStorySnippets::getCurrentUserIdentitySnippet();
            $_SESSION['sent-fullstory-user-identity'] = FullStoryIdentity::getRealUserId();
        }

        // User metadata for FullStory.
        echo FullStorySnippets::getCurrentUserMetadataSnippet();

        // For dev / QA / debugging.
        if (self::isDebugMarkupEnabled()) {
            echo FullStorySnippets::getDebugMarkupSnippet();
        }

        return true;
    }

    /**
     * Determine if FullStory is enabled for the current user.
     *
     * @return bool True if enabled. False if not.
     */
    public static function isFullStoryEnabled(): bool
    {
        if ('true' != getenv('FULLSTORY_ENABLED')) {
            return false;
        }

        $fullStoryMode = getenv('FULLSTORY_MODE') ?: 'everyone';
        switch ($fullStoryMode) {
            case 'everyone':
                return true;
            case 'educators':
                $loggedInUserRole = FullStoryIdentity::getUserRole();
                // "pending-approval" is included because only instructors can request an account.
                if (in_array($loggedInUserRole, ['pending-approval', 'instructor', 'limited-course-creator', 'group-admin'])) {
                    return true;
                }
                break;
            case 'students':
                $loggedInUserRole = FullStoryIdentity::getUserRole();
                if (in_array($loggedInUserRole, ['student', 'tutor'])) {
                    return true;
                }
                break;
        }

        return false;
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

}
