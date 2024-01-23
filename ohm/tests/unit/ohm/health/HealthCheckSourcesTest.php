<?php

namespace OHM\tests\unit\ohm\health;

use OHM\health\HealthCheckSources;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class HealthCheckSourcesTest extends TestCase
{
    /** @var PDO $dbh */
    private PDO $dbh;
    private HealthCheckSources $healthCheckSources;

    function setUp(): void
    {
        $this->dbh = $this->createMock(PDO::class);

        $this->healthCheckSources = new HealthCheckSources($this->dbh);
    }

    function test_fetch_grade_passback_queue_size(): void
    {
        $pdoStatement = $this->createMock(PDOStatement::class);

        $pdoStatement->expects($this->once())
            ->method('fetchColumn')->willReturn(326);
        $this->dbh->expects($this->once())
            ->method('prepare')->willReturn($pdoStatement);

        $queueSize = $this->healthCheckSources->fetch_grade_passback_queue_size();
        $this->assertEquals(326, $queueSize);
    }
}
