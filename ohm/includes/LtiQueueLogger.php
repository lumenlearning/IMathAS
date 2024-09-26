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

        $timeStr = strftime('%Y-%b-%d %H:%M:%S %Z', time());
        $message = sprintf('[%s] %s' . "\n", $timeStr, $logText);

        if (empty($lti_response_log_file)) {
            error_log($message);
        } else {
            file_put_contents($lti_response_log_file, $message, FILE_APPEND);
        }
    }

    /**
     * Get a user ID and assessment ID from a hash. The hash value should
     * come from the "hash" column in imas_ltiqueue.
     *
     * @param ?string $hash A string that looks like "1234-4321".
     * @return array The user ID and assessment ID, if found. Nulls if not found.
     */
    public static function getUserIdAndAssessmentIdFromHash(?string $hash): array
    {
        if (empty($hash)) {
            return [
                'assessment_id' => null,
                'user_id' => null,
            ];
        }

        [$assessmentid, $userid] = [null, null];
        $hashParts = explode('-', $hash);
        if (2 == count($hashParts)
            && !empty((int)$hashParts[0])
            && !empty((int)$hashParts[1])
        ) {
            $assessmentid = (int)$hashParts[0];
            $userid = (int)$hashParts[1];
        }

        return [
            'assessment_id' => $assessmentid,
            'user_id' => $userid,
        ];
    }

    /**
     * Return an array containing details useful for logging from an imas_ltiqueue row.
     *
     * This method attempts to return a user ID and assessment ID by looking at
     * hash values and row data.
     *
     * @param array $row An associative array containing a single row from imas_ltiqueue.
     * @return array An associative array with useful details for logging.
     */
    public static function generateLtiqueueRowDetails(array $row): array
    {
        /*
         * "??" and "?:" are used very defensively in this method to try
         * and protect against bad data.
         */

        $hash = $row['hash'] ?? null;

        // Attempt to get the user ID and assessment ID from the hash.
        $aidAndUid = self::getUserIdAndAssessmentIdFromHash($hash);
        $useridFromHash = $aidAndUid['user_id'];
        $assessmentidFromHash = $aidAndUid['assessment_id'];

        // Defend against null and "0" value IDs while preferring row
        // values for the user and assessment IDs.
        $userid = $row['userid'] ?: $useridFromHash;
        $assessmentid = $row['assessmentid'] ?: $assessmentidFromHash;

        $rowDetails = [
            'hash' => $hash,
            'sourcedid' => $row['sourcedid'] ?? null,
            'userid' => $userid,
            'assessmentid' => $assessmentid,
            'isstu' => $row['isstu'] ?? null,
            'grade' => $row['grade'] ?? null,
            'failures' => $row['failures'] ?? null,
            'addedon' => $row['addedon'] ?? null,
        ];
        return $rowDetails;
    }
}
