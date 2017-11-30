<?php

namespace OHM;

require_once(__DIR__ . '/../includes/StudentPaymentDb.php');
require_once(__DIR__ . "/../../ohm/mocks/PDOMock.php");
require_once(__DIR__ . "/../../ohm/mocks/PDOStatementMock.php");

use PHPUnit\Framework\TestCase;


/**
 * @covers StudentPaymentDb
 */
final class StudentPaymentDbTest extends TestCase
{

	private $studentPaymentDb;

	private $pdoMock;
	private $pdoStatementMock;


	function setUp()
	{
		$this->pdoMock = $this->createMock(PDOMock::class);
		$this->pdoStatementMock = $this->createMock(PDOStatementMock::class);

		$this->studentPaymentDb = new StudentPaymentDb(42, 2604, 128);
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

}

