<?php

namespace OHM\Tests;

use OHM\Services\OptOutService;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use RuntimeException;


/**
 * @covers EulaService
 */
final class OptOutServiceTest extends TestCase
{
    /* @var OptOutService */
    private $optOutService;

    /* @var PDO */
    private $dbh;

    function setUp(): void
    {
        $this->dbh = $this->createMock(PDO::class);

        $this->optOutService = new OptOutService($this->dbh);
    }

    /*
     * isOptedOutOfAssessments
     */

    function testIsOptedOutOfAssessments_False(): void
    {
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->expects($this->once())
            ->method('fetchColumn')->willReturn(0);
        $pdoStatement->expects($this->once())
            ->method('execute')->willReturn(true);
        $this->dbh->expects($this->once())
            ->method('prepare')->willReturn($pdoStatement);

        $result = $this->optOutService->isOptedOutOfAssessments(123, 42);
        $this->assertFalse($result);
    }

    function testIsOptedOutOfAssessments_True(): void
    {
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->expects($this->once())
            ->method('fetchColumn')->willReturn(1);
        $pdoStatement->expects($this->once())
            ->method('execute')->willReturn(true);
        $this->dbh->expects($this->once())
            ->method('prepare')->willReturn($pdoStatement);

        $result = $this->optOutService->isOptedOutOfAssessments(123, 42);
        $this->assertTrue($result);
    }

    function testIsOptedOutOfAssessments_EnrollmentNotFound(): void
    {
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->expects($this->once())
            ->method('execute')->willReturn(false);
        $this->dbh->expects($this->once())
            ->method('prepare')->willReturn($pdoStatement);
        $this->dbh->expects($this->once())
            ->method('errorInfo')->willReturn(['meow', 'lol']);

        $result = $this->optOutService->isOptedOutOfAssessments(123, 42);
        $this->assertFalse($result);
    }

    function testIsOptedOutOfAssessments_UserIdZero(): void
    {
        $this->expectException(RuntimeException::class);
        $this->optOutService->isOptedOutOfAssessments(0, 42);
    }

    function testIsOptedOutOfAssessments_CourseIdZero(): void
    {
        $this->expectException(RuntimeException::class);
        $this->optOutService->isOptedOutOfAssessments(123, 0);
    }
}
