<?php

namespace OHM\Tests;

use PHPUnit\Framework\TestCase;

use OHM\Mocks\PDOMock;
use OHM\Mocks\PDOStatementMock;

use OHM\Includes\StudentPaymentDb;
use OHM\Exceptions\StudentPaymentException;


/**
 * @covers StudentPaymentDb
 */
final class StudentPaymentDbTest extends TestCase
{

	private $studentPaymentDb;

	private $pdoMock;
	private $pdoStatementMock;


	function setUp(): void
	{
		$this->pdoMock = $this->createMock(PDOMock::class);
		$this->pdoStatementMock = $this->createMock(PDOStatementMock::class);

		$this->studentPaymentDb = new StudentPaymentDb(42, 2604, 128, 42, null);
		$this->studentPaymentDb->setDbh($this->pdoMock);
	}

	/*
	 * getStudentGroupId
	 */

	public function testGetStudentGroupId()
	{
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetchColumn')->willReturn(42); // return a group ID

		$result = $this->studentPaymentDb->getStudentGroupId();

		$this->assertEquals(42, $result);
	}

    public function testGetStudentGroupId_StudentIdNotFound()
    {
        $pdoMock = $this->createMock(PDOMock::class);
        $pdoStatementMock = $this->createMock(PDOStatementMock::class);

        $pdoMock->method('prepare')->willReturn($pdoStatementMock);
        $pdoStatementMock->method('fetchColumn')->willReturn(null); // return a group ID

        $this->expectException(StudentPaymentException::class);

        $studentPaymentDb = new StudentPaymentDb(42, 2604, 128, 42, null);
        $studentPaymentDb->setDbh($pdoMock);
        $studentPaymentDb->getStudentGroupId();
    }

	/*
	 * getStudentEnrollmentId
	 */

	public function testGetStudentEnrollmentId()
	{
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetchColumn')->willReturn(42); // return a group ID

		$result = $this->studentPaymentDb->getStudentEnrollmentId();

		$this->assertEquals(42, $result);
	}

    public function testGetStudentEnrollmentId_EnrollmentIdNotFound()
    {
        $pdoMock = $this->createMock(PDOMock::class);
        $pdoStatementMock = $this->createMock(PDOStatementMock::class);

        $pdoMock->method('prepare')->willReturn($pdoStatementMock);
        $pdoStatementMock->method('fetchColumn')->willReturn(null); // return a group ID

        $this->expectException(StudentPaymentException::class);

        $studentPaymentDb = new StudentPaymentDb(42, 2604, 128, 42, null);
        $studentPaymentDb->setDbh($pdoMock);
        $studentPaymentDb->getStudentEnrollmentId();
    }

	/*
	 * getStudentHasActivationCode
	 */

	public function testGetStudentHasActivationCode()
	{
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetchColumn')->willReturn(true); // return a group ID

		$result = $this->studentPaymentDb->getStudentHasActivationCode();

		$this->assertTrue($result);
	}

	/*
	 * setStudentHasActivationCode
	 */

	public function testSetStudentHasActivationCode_InvalidType()
	{
		$this->expectException(StudentPaymentException::class);

		$this->studentPaymentDb->setStudentHasActivationCode("asdf");
	}

	/*
	 * getCourseRequiresStudentPayment
	 */

	public function testGetCourseRequiresStudentPayment()
	{
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetchColumn')->willReturn(true); // return a group ID

		$result = $this->studentPaymentDb->getCourseRequiresStudentPayment();

		$this->assertTrue($result);
	}

	/*
	 * setCourseRequiresStudentPayment
	 */

	public function testSetCourseRequiresStudentPayment()
	{
		$this->expectException(StudentPaymentException::class);

		$this->studentPaymentDb->setCourseRequiresStudentPayment("asdf");
	}

	/*
	 * getGroupIdForPayments
	 */

    public function testGetGroupIdForPayments_byCourseOwnerGroupId() {
        $studentPaymentDb = new StudentPaymentDb(null, null, null, 42, null);

        $groupId = $studentPaymentDb->getGroupIdForPayments();

        $this->assertEquals(42, $groupId);
    }

    public function testGetGroupIdForPayments_byCourseOwnerUserId() {
        $studentPaymentDb = new StudentPaymentDb(null, null, null, null, 123);
        $studentPaymentDb->setDbh($this->pdoMock);
        $this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
        $this->pdoStatementMock->method('fetch')->willReturn(['groupid' => 42]);

        $groupId = $studentPaymentDb->getGroupIdForPayments();

        $this->assertEquals(42, $groupId);
    }

    public function testGetGroupIdForPayments_byCourseId() {
        $studentPaymentDb = new StudentPaymentDb(null, 123, null, null, null);
        $studentPaymentDb->setDbh($this->pdoMock);
        $this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
        $this->pdoStatementMock->method('fetch')->willReturn(['groupid' => 42]);

        $groupId = $studentPaymentDb->getGroupIdForPayments();

        $this->assertEquals(42, $groupId);
    }

    public function testGetGroupIdForPayments_byStudentGroupId() {
        $studentPaymentDb = new StudentPaymentDb(42, null, null, null, null);

        $groupId = $studentPaymentDb->getGroupIdForPayments();

        $this->assertEquals(42, $groupId);
    }

    public function testGetGroupIdForPayments_byStudentUserId() {
        $studentPaymentDb = new StudentPaymentDb(null, null, 12345, null, null);
        $studentPaymentDb->setDbh($this->pdoMock);
        $this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
        $this->pdoStatementMock->method('fetchColumn')->willReturn(42);

        $groupId = $studentPaymentDb->getGroupIdForPayments();

        $this->assertEquals(42, $groupId);
    }

    public function testGetGroupIdForPayments_InsufficientData() {
        $this->expectException(StudentPaymentException::class);

        $studentPaymentDb = new StudentPaymentDb(null, null, null, null, null);
        $studentPaymentDb->getGroupIdForPayments();
    }

	/*
	 * getGroupRequiresStudentPayment
	 */

	public function testGetGroupRequiresStudentPayment()
	{
		// Mock return data
		$dbResult = array('student_pay_enabled' => true);

		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetchColumn')->willReturn(42); // return a group ID
		$this->pdoStatementMock->method('fetch')->willReturn($dbResult);

		$result = $this->studentPaymentDb->getGroupRequiresStudentPayment();

		$this->assertTrue($result);
	}

	public function testGetGroupRequiresStudentPayment_MissingData()
	{
		// Mock return data
		$dbResult = array('student_pay_enabled' => null, 'name' => 'Awesome University');

		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetchColumn')->willReturn(42); // return a group ID
		$this->pdoStatementMock->method('fetch')->willReturn($dbResult);

		$this->expectException(StudentPaymentException::class);

		$this->studentPaymentDb->getGroupRequiresStudentPayment();
	}

	/*
	 * setGroupRequiresStudentPayment
	 */

	public function testSetGroupRequiresStudentPayment()
	{
		$this->expectException(StudentPaymentException::class);

		$this->studentPaymentDb->setGroupRequiresStudentPayment("asdf");
	}

	/*
	 * setStudentPaymentAllCoursesByGroupId
	 */

	public function testSetStudentPaymentAllCoursesByGroupId()
	{
		// Mock return data
		$dbResult = array(42);

		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetch')->willReturn($dbResult);

		$result = $this->studentPaymentDb->setStudentPaymentAllCoursesByGroupId(1234, true);

		$this->assertTrue($result);
	}

	/*
	 * getCourseOwnerGroupId
	 */

	public function testGetCourseOwnerGroupId()
	{
		$dbResult = array('groupid' => 42);

		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetch')->willReturn($dbResult);

		$result = $this->studentPaymentDb->getCourseOwnerGroupId();

		$this->assertEquals(42, $result);
	}

    public function testGetCourseOwnerGroupId_NoCourseId()
    {
        $this->expectException(StudentPaymentException::class);

        $studentPaymentDb = new StudentPaymentDb(null, null, null, null, null);
        $studentPaymentDb->getCourseOwnerGroupId();
    }

    /*
     * getCourseOwnerGroupName
     */

    public function testGetCourseOwnerGroupName()
    {
        $dbResult = array('name' => 'Meow School of Meowing');

        $this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
        $this->pdoStatementMock->method('fetch')->willReturn($dbResult);

        $result = $this->studentPaymentDb->getCourseOwnerGroupName();

        $this->assertEquals('Meow School of Meowing', $result);
    }

    public function testGetCourseOwnerGroupName_NoCourseId()
    {
        $this->expectException(StudentPaymentException::class);

        $studentPaymentDb = new StudentPaymentDb(null, null, null, null, null);
        $studentPaymentDb->getCourseOwnerGroupName();
    }

	/*
	 * getGroupIdByUserId
	 */

    public function testGetGroupIdByUserId() {
        $dbResult = array('groupid' => 42);

        $this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
        $this->pdoStatementMock->method('fetch')->willReturn($dbResult);

        $result = $this->studentPaymentDb->getGroupIdByUserId(123);

        $this->assertEquals(42, $result);
    }

	/*
	 * getGroupGuid
	 */

	public function testGetGroupGuid()
	{
		$dbResult = array('lumen_guid' => 42);

		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetch')->willReturn($dbResult);

		$result = $this->studentPaymentDb->getGroupGuid(1234);

		$this->assertEquals(42, $result);
	}

	/*
	 * getLumenGuid
	 */

	public function testGetLumenGuid()
	{
		$dbResult = array('lumen_guid' => '2826e8e4-8d79-4c45-a8d6-0ef862496bc1');

		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetch')->willReturn($dbResult);

		$result = $this->studentPaymentDb->getLumenGuid();

		$this->assertEquals($dbResult['lumen_guid'], $result);
	}

}

