<?php

namespace OHM\Tracking;

/**
 * This class loads FullStory and provides user metadata to FullStory.
 *
 * Example data provided to FullStory: User ID, Course ID + name, etc.
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
        global $basesiteurl;

        if ('true' != getenv('FULLSTORY_ENABLED')) {
            return false;
        }

        printf('<script src="%s/ohm/js/fullstory.js"></script>' . "\n",
            $basesiteurl);

        return true;
    }
}
