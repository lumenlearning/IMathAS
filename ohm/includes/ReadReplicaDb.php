<?php

namespace OHM\Includes;

use Exception;
use PDO;
use PDOException;

/**
 * Class ReadReplicaDb Creates and return a PDO object for the OHM read replica DB.
 */
class ReadReplicaDb
{
    /**
     * Connect to the OHM read replica database and return a PDO connection.
     *
     * @return PDO
     * @throws Exception Thrown if unable to connect to the read replica DB.
     */
    public static function getPdoInstance(): PDO
    {
        // Connect to DB read replica for report generation.
        try {
            $dbh = new PDO(
                sprintf('mysql:host=%s;dbname=%s',
                    getenv('REPLICA_DB_SERVER'),
                    getenv('REPLICA_DB_NAME')
                ),
                getenv('REPLICA_DB_USERNAME'),
                getenv('REPLICA_DB_PASSWORD')
            );
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception('Cound not connect to replica database: '
                . $e->getMessage());
        }
        // Test the connection.
        $dbh->query("set session sql_mode=''");

        return $dbh;
    }
}
