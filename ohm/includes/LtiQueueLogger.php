<?php

namespace OHM\Includes;

class LtiQueueLogger
{
    /**
     * Log a message to the LTI grade passback debug log.
     *
     * processltiqueue.php currently requires this method to be static.
     *
     * @param string $logText The message to log.
     * @return void
     */
    public static function debug_log(string $logText): void
    {
        $lti_response_log_file = getenv('LTI_RESPONSE_LOG_FILE');
        if (empty($lti_response_log_file)) {
            return;
        }

        $timeStr = strftime('%Y-%b-%d %H:%M:%S %Z', time());
        file_put_contents(
            $lti_response_log_file,
            sprintf('[%s] %s' . "\n", $timeStr, $logText),
            FILE_APPEND
        );
    }
}
