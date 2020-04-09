<?php
/**
 * This "hook" file ensures the replica DB is used for report generation by
 * replacing the default global $DBH with a PDO connection to the replica DB.
 */

use OHM\Includes\ReadReplicaDb;

require_once(__DIR__ . '/../ohm/includes/ReadReplicaDb.php');

$GLOBALS['DBH'] = ReadReplicaDb::getPdoInstance();

/**
 * Notify the user we are using a read replica DB for reporting.
 */
function displayReportNotice()
{
    echo '<p><u>Note</u>: Data is queried from the OHM read replica DB.</p>';
}
