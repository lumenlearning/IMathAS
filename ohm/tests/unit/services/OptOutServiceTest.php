<?php

namespace OHM\Tests;

use OHM\Services\OptOutService;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
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

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     * @return mixed Method return.
     * @throws ReflectionException
     */
    function invokePrivateMethod(object &$object, string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
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

    /*
     * isCsvFile
     */

    function testIsCsvFile_ValidCsvFile(): void
    {
        $isVaildCsvFile = $this->optOutService->isCsvFile(__DIR__ . '/../../fixtures/optout/student_list_valid.csv');
        $this->assertTrue($isVaildCsvFile);
    }

    function testIsCsvFile_InvalidCsvFile(): void
    {
        $isVaildCsvFile = $this->optOutService->isCsvFile(__DIR__ . '/../../fixtures/optout/student_list_invalid.csv');
        $this->assertFalse($isVaildCsvFile);
    }

    /*
     * setStudentOptedOut
     */

    function testSetStudentOptedOut_EnrollmentFound(): void
    {
        // The method under test executes an UPDATE statement
        // and checks the affected row count for success.
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->expects($this->once())
            ->method('rowCount')->willReturn(1);
        $pdoStatement->expects($this->once())
            ->method('execute')->willReturn(true);
        $this->dbh->expects($this->once())
            ->method('prepare')->willReturn($pdoStatement);

        // Call the method under test.
        $result = $this->invokePrivateMethod($this->optOutService,
            'setStudentOptedOut', array(1234, 42, true));

        $this->assertTrue($result['optOutStateIsUpdated']);
        $this->assertEmpty($result['errors']);
    }

    function testSetStudentOptedOut_EnrollmentNotFound(): void
    {
        // The method under test executes an UPDATE statement
        // and checks the affected row count for success.
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->expects($this->once())
            ->method('rowCount')->willReturn(0);
        $pdoStatement->expects($this->once())
            ->method('execute')->willReturn(true);
        $this->dbh->expects($this->once())
            ->method('prepare')->willReturn($pdoStatement);

        // Call the method under test.
        $result = $this->invokePrivateMethod($this->optOutService,
            'setStudentOptedOut', array(1234, 42, true));

        $this->assertFalse($result['optOutStateIsUpdated']);
        $this->assertCount(1, $result['errors']);
        $this->assertEquals('Enrollment record not found for user ID 1234 and course ID 42.',
            $result['errors'][0]);
    }

    /*
     * setStudentOptedOutByEnrollmentId
     */

    function testSetStudentOptedOutByEnrollmentId_EnrollmentFound(): void
    {
        // The method under test executes an UPDATE statement
        // and checks the affected row count for success.
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->expects($this->once())
            ->method('rowCount')->willReturn(1);
        $pdoStatement->expects($this->once())
            ->method('execute')->willReturn(true);
        $this->dbh->expects($this->once())
            ->method('prepare')->willReturn($pdoStatement);

        // Call the method under test.
        $result = $this->invokePrivateMethod($this->optOutService,
            'setStudentOptedOutByEnrollmentId', array(42, true));

        $this->assertTrue($result['optOutStateIsUpdated']);
        $this->assertEmpty($result['errors']);
    }

    function testSetStudentOptedOutByEnrollmentId_EnrollmentNotFound(): void
    {
        // The method under test executes an UPDATE statement
        // and checks the affected row count for success.
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->expects($this->once())
            ->method('rowCount')->willReturn(0);
        $pdoStatement->expects($this->once())
            ->method('execute')->willReturn(true);
        $this->dbh->expects($this->once())
            ->method('prepare')->willReturn($pdoStatement);

        // Call the method under test.
        $result = $this->invokePrivateMethod($this->optOutService,
            'setStudentOptedOutByEnrollmentId', array(42, true));

        $this->assertFalse($result['optOutStateIsUpdated']);
        $this->assertCount(1, $result['errors']);
        $this->assertEquals('Enrollment record not found for enrollment ID: 42', $result['errors'][0]);
    }

    /*
     * getOptOutStatus
     */

    function testGetOptOutStatus_NotFound(): void
    {
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->expects($this->exactly(2))
            ->method('rowCount')->willReturn(0);
        $pdoStatement->expects($this->exactly(2))
            ->method('execute')->willReturn(true);
        $this->dbh->expects($this->exactly(2))
            ->method('prepare')->willReturn($pdoStatement);

        /*
         * With email address.
         */

        // Call the method under test.
        $result = $this->invokePrivateMethod($this->optOutService,
            'getOptOutStatus', [42, 123, 'FirstName', 'LastName', 'email@example.com']);

        $this->assertNull($result);

        /*
         * Without email address.
         */

        // Call the method under test.
        $result = $this->invokePrivateMethod($this->optOutService,
            'getOptOutStatus', [42, 123, 'FirstName', 'LastName']);

        $this->assertNull($result);
    }

    function testGetOptOutStatus_FoundNotOptedOut(): void
    {
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->expects($this->exactly(2))
            ->method('fetch')->willReturn([
                'id' => 42,
                'is_opted_out_assessments' => 0
            ]);
        $pdoStatement->expects($this->exactly(2))
            ->method('rowCount')->willReturn(1);
        $pdoStatement->expects($this->exactly(2))
            ->method('execute')->willReturn(true);
        $this->dbh->expects($this->exactly(2))
            ->method('prepare')->willReturn($pdoStatement);

        /*
         * With email address.
         */

        // Call the method under test.
        $result = $this->invokePrivateMethod($this->optOutService,
            'getOptOutStatus', [42, 123, 'FirstName', 'LastName', 'email@example.com']);

        $this->assertEquals(42, $result['enrollmentId']);
        $this->assertFalse($result['optOutState']);

        /*
         * Without email address.
         */

        // Call the method under test.
        $result = $this->invokePrivateMethod($this->optOutService,
            'getOptOutStatus', [42, 123, 'FirstName', 'LastName']);

        $this->assertEquals(42, $result['enrollmentId']);
        $this->assertFalse($result['optOutState']);
    }

    function testGetOptOutStatus_FoundOptedOut(): void
    {
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->expects($this->exactly(2))
            ->method('fetch')->willReturn([
                'id' => 42,
                'is_opted_out_assessments' => 1
            ]);
        $pdoStatement->expects($this->exactly(2))
            ->method('rowCount')->willReturn(1);
        $pdoStatement->expects($this->exactly(2))
            ->method('execute')->willReturn(true);
        $this->dbh->expects($this->exactly(2))
            ->method('prepare')->willReturn($pdoStatement);

        /*
         * With email address.
         */

        // Call the method under test.
        $result = $this->invokePrivateMethod($this->optOutService,
            'getOptOutStatus', [42, 123, 'FirstName', 'LastName', 'email@example.com']);

        $this->assertEquals(42, $result['enrollmentId']);
        $this->assertTrue($result['optOutState']);

        /*
         * Without email address.
         */

        // Call the method under test.
        $result = $this->invokePrivateMethod($this->optOutService,
            'getOptOutStatus', [42, 123, 'FirstName', 'LastName']);

        $this->assertEquals(42, $result['enrollmentId']);
        $this->assertTrue($result['optOutState']);
    }
}
