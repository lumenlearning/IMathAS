<?php

namespace OHM\Includes;

use PDOStatement;

class LtiQueueValidator
{
    // This is the value that will cause processltiqueue.php to skip
    // rows in imas_ltiqueue.
    const LTI_QUEUE_MAX_FAIL_COUNT = 7;

    /**
     * Determine if a sourcedid is valid. Covers LTI 1.1 and 1.3.
     *
     * @param string $hash The hash column for the sourcedid.
     * @param string $sourcedid The sourcedid to validate.
     * @return bool True if the sourcedid is valid. False if not.
     */
    public function is_valid_sourcedid(string $hash, string $sourcedid): bool
    {
        if (empty($hash)) {
            $message = sprintf('Invalid (empty) hash. $sourcedid = "%s"', $sourcedid);
            LtiQueueLogger::debug_log($message);
            return false;
        }

        if (empty($sourcedid)) {
            $message = sprintf('Invalid (empty) sourcedid. Hash = %s', $hash);
            LtiQueueLogger::debug_log($message);
            return false;
        }

        /*
         * - Using "substr" is how IMathAS currently checks for an LTI 1.3 sourcedid.
         * - The "else" condition is how an LTI 1.1 sourcedid is matched in IMathAS.
         */
        if ('LTI1.3' == substr($sourcedid, 0, 6)) {
            return $this->is_valid_sourcedid_lti_1_3($hash, $sourcedid);
        } else {
            return $this->is_valid_sourcedid_lti_1_1($hash, $sourcedid);
        }
    }

    /**
     * Set a row in imas_ltiqueue as invalid.
     *
     * This is currently done by setting its "failures" column to 7,
     * the maximum number of allowed failures. This causes the
     * processltiqueue.php file to skip over these rows.
     *
     * @param string $hash The value of the "hash" column for the row.
     * @return bool True on success. False on failure.
     */
    public function set_ltiqueue_row_invalid(string $hash): bool
    {
        global $DBH;

        // We can't do anything if $DBH isn't set.
        if (empty($DBH)) {
            $message = sprintf(
                '$GLOBALS[\'DBH\'] is not set. Unable to permanently fail row in imas_ltiqueue for hash: "%s"',
                $hash
            );
            LtiQueueLogger::debug_log($message);
            return false;
        }

        /*
         * Permanently fail a grade return row in imas_ltiqueue.
         */

        /** @var PDOStatement $stm */
        $stm = $DBH->prepare('UPDATE imas_ltiqueue SET sendon = ? WHERE hash = ?');
        $stm->execute([self::LTI_QUEUE_MAX_FAIL_COUNT, $hash]);

        /*
         * If no rows were affected, log the failure.
         */

        if (1 > $stm->rowCount()) {
            $message = sprintf(
                'Failed to set "failures" to %d for hash %s. (UPDATE statement affected 0 rows)',
                self::LTI_QUEUE_MAX_FAIL_COUNT, $hash
            );
            LtiQueueLogger::debug_log($message);
            return false;
        }

        /*
         * Success!
         */

        $message = sprintf(
            'Set "failures" to %d for hash %s. This grade passback will not be processed.',
            self::LTI_QUEUE_MAX_FAIL_COUNT, $hash
        );
        LtiQueueLogger::debug_log($message);

        return true;
    }

    /**
     * Determine if an LTI 1.1 sourcedid is valid.
     *
     * @param string $hash The hash column for the sourcedid.
     * @param string $sourcedid The sourcedid to validate.
     * @return bool True if the sourcedid is valid. False if not.
     */
    private function is_valid_sourcedid_lti_1_1(string $hash, string $sourcedid): bool
    {
        // Split the sourcedid so we can count its parts.
        $sourcedid_parts = explode(':|:', $sourcedid);

        // processltiqueue.php requires 4 parts in the sourcedid.
        if (4 != count($sourcedid_parts)) {
            $message = sprintf(
                '(LTI 1.1) sourcedid is incomplete (4 parts not found). Hash = %s, $sourcedid = "%s"',
                $hash, $sourcedid
            );
            LtiQueueLogger::debug_log($message);
            return false;
        }

        list($lti_sourcedid, $ltiurl, $ltikey, $keytype) = $sourcedid_parts;

        // No LMS will accept an empty lti_sourcedid.
        if (empty($lti_sourcedid)) {
            $message = sprintf(
                '(LTI 1.1) Invalid (empty) lti_sourcedid. Hash = %s, $lti_sourcedid = "%s", $sourcedid = "%s"',
                $hash, $lti_sourcedid, $sourcedid
            );
            LtiQueueLogger::debug_log($message);
            return false;
        }

        // We can't pass a grade back without a valid URL.
        if (empty($ltiurl)) {
            $message = sprintf(
                '(LTI 1.1) Invalid (empty) ltiurl. Hash = %s, $sourcedid = "%s"',
                $hash, $sourcedid
            );
            LtiQueueLogger::debug_log($message);
            return false;
        }
        if (false === filter_var($ltiurl, FILTER_VALIDATE_URL)) {
            $message = sprintf(
                '(LTI 1.1) Invalid score_url. Hash = %s, $ltiurl = "%s", $sourcedid = "%s"',
                $hash, $ltiurl, $sourcedid
            );
            LtiQueueLogger::debug_log($message);
            return false;
        }

        // An ltikey is required by the LMS.
        if (empty($ltikey)) {
            $message = sprintf(
                '(LTI 1.1) Invalid (empty) ltikey. Hash = %s, $sourcedid = "%s"',
                $hash, $sourcedid
            );
            LtiQueueLogger::debug_log($message);
            return false;
        }

        /*
         * The validation for $keytype is commented out because I'm
         * currently unable to determine if 'c' and 'u' are the only
         * valid key types, or if blank keytypes are also valid.
         *
         * Keytype 'c' appears to be a cource-level keytype, where
         * the key looks like "meow_courseid". Example: "asdf_42"
         */

        // Check for a valid keytype.
//        if (empty($keytype)) {
//            $message = sprintf(
//                '(LTI 1.1) Invalid (empty) keytype. Hash = %s, $sourcedid = "%s"',
//                $hash, $sourcedid
//            );
//            LtiQueueLogger::debug_log($message);
//            return false;
//        }
//        if (!in_array($keytype, ['u', 'c'])) {
//            $message = sprintf(
//                '(LTI 1.1) Invalid keytype. Hash = %s, $keytype = "%s", $sourcedid = "%s"',
//                $hash, $keytype, $sourcedid
//            );
//            LtiQueueLogger::debug_log($message);
//            return false;
//        }

        return true;
    }


    /**
     * Determine if an LTI 1.3 sourcedid is valid.
     *
     * @param string $hash The hash column for the sourcedid.
     * @param string $sourcedid The sourcedid to validate.
     * @return bool True if the sourcedid is valid. False if not.
     */
    private function is_valid_sourcedid_lti_1_3(string $hash, string $sourcedid): bool
    {
        // Split the sourcedid to count its parts.
        // Do this separately from how IMathAS does it so we can safely
        // count the number of parts if there are less than four.
        $sourcedid_parts = explode(':|:', $sourcedid);

        // processltiqueue.php requires 4 parts in the sourcedid.
        if (4 != count($sourcedid_parts)) {
            $message = sprintf(
                '(LTI 1.3) sourcedid is incomplete (4 parts not found). Hash = %s, $sourcedid = "%s"',
                $hash, $sourcedid
            );
            LtiQueueLogger::debug_log($message);
            return false;
        }

        // This is how IMathAS splits the sourcedid into separate variables.
        list($ltiver, $ltiuserid, $score_url, $platformid) = explode(':|:', $sourcedid);

        // Ensure the exploded string matches the string returned by substr.
        if ('LTI1.3' != $ltiver) {
            $message = sprintf(
                '(LTI 1.3) Invalid "LTI1.3" string. Hash = %s, $ltiver = "%s", $sourcedid = "%s"',
                $hash, $ltiver, $sourcedid
            );
            LtiQueueLogger::debug_log($message);
            return false;
        }

        // A valid ltiuserid is required by the LMS.
        if (empty($ltiuserid)) {
            $message = sprintf(
                '(LTI 1.3) Invalid (empty) ltiuserid. Hash = %s, $sourcedid = "%s"',
                $hash, $sourcedid
            );
            LtiQueueLogger::debug_log($message);
            return false;
        }

        // We can't pass a grade back without a valid URL.
        if (empty($score_url)) {
            $message = sprintf(
                '(LTI 1.3) Invalid (empty) score_url. Hash = %s, $sourcedid = "%s"',
                $hash, $sourcedid
            );
            LtiQueueLogger::debug_log($message);
            return false;
        }
        if (false === filter_var($score_url, FILTER_VALIDATE_URL)) {
            $message = sprintf(
                '(LTI 1.3) Invalid score_url. Hash = %s, $score_url = "%s", $sourcedid = "%s"',
                $hash, $score_url, $sourcedid
            );
            LtiQueueLogger::debug_log($message);
            return false;
        }

        // We can't lookup access tokens without a valid platform ID.
        if (empty($platformid)) {
            $message = sprintf(
                '(LTI 1.3) Invalid (empty) platformid. Hash = %s, $sourcedid = "%s"',
                $hash, $sourcedid
            );
            LtiQueueLogger::debug_log($message);
            return false;
        }
        if (!is_numeric($platformid) || (int)$platformid != $platformid) {
            // LTI_Grade_Update->have_token() requires integers.
            $message = sprintf(
                '(LTI 1.3) Invalid (not an integer) platformid. Hash = %s, $platformid = "%s", $sourcedid = "%s"',
                $hash, $platformid, $sourcedid
            );
            LtiQueueLogger::debug_log($message);
            return false;
        }

        // All validations were successful.
        return true;
    }
}
