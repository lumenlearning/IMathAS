<?php

namespace OHM\health;

use PDO;
use PDOStatement;

class HealthCheckSources
{
    private PDO $dbh;

    public function __construct(PDO $dbh)
    {
        $this->dbh = $dbh;
    }

    /**
     * Get the number of grades waiting to be passed back in imas_ltiqueue.
     *
     * @return int The number of grades waiting to be returned.
     */
    public function fetch_grade_passback_queue_size(): int
    {
        /** @var PDOStatement $stm */
        $stm = $this->dbh->prepare('SELECT COUNT(hash) FROM imas_ltiqueue WHERE failures < 7');
        $stm->execute();
        $queueSize = $stm->fetchColumn();

        return $queueSize;
    }
}
