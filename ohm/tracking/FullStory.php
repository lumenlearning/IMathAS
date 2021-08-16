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
        $snippet = self::getHeaderSnippet();

        if ('' == $snippet) {
            return false;
        } else {
            echo $snippet;
            return true;
        }
    }

    /**
     * Get the JS snippet required to load FullStory during a user's
     * session.
     *
     * This only returns HTML elements as a string. It does not output
     * anything to the user.
     *
     * This string should be inserted into the <head> section of a web page.
     *
     * @return string An HTML snippet required to load FullStory on a page.
     *                An empty string if FullStory is not enabled.
     */
    public static function getHeaderSnippet(): string
    {
        global $basesiteurl, $myrights, $configEnvironment;

        if ('true' != getenv('FULLSTORY_ENABLED')) {
            return '';
        }

        $snippet = sprintf(
            '<script src="%s/ohm/js/fullstory.js"></script>' . "\n",
            $basesiteurl);

        // This assists QA & devs to ensure sensitive user information
        // is properly excluded or masked during non-production testing.
        if (100 == $myrights && 'production' != $configEnvironment) {
            $snippet .= sprintf(
                '<link rel="stylesheet" href="%s/ohm/tracking/sensitive_info_highlight.css">' . "\n",
                $basesiteurl);
        }

        return $snippet;
    }
}
