<?php

namespace OHM;

require_once(__DIR__ . '/../models/StudentPayStatus.php');
require_once(__DIR__ . '/../models/StudentPayApiResult.php');
require_once(__DIR__ . '/../includes/StudentPayment.php');

use PHPUnit\Framework\TestCase;

$GLOBALS['student_pay_api']['base_url'] = 'http://127.0.0.1:5000/student_auth/v1';
$GLOBALS['student_pay_api']['timeout'] = 10;
$GLOBALS['student_pay_api']['jwt_secret'] = 'phptest_secret_goes_here';


/**
 * @covers StudentPayment
 */
final class StudentPaymentTest extends TestCase
{

	private $studentPayment;

	private $studentPaymentApiMock;
	private $studentPaymentDbMock;


	function setUp()
	{
		$this->studentPaymentApiMock = $this->createMock(StudentPaymentApi::class);
		$this->studentPaymentDbMock = $this->createMock(StudentPaymentDb::class);

		$this->studentPayment = new StudentPayment(42, 2604, 128);
		$this->studentPayment->setStudentPaymentApi($this->studentPaymentApiMock);
		$this->studentPayment->setStudentPaymentDb($this->studentPaymentDbMock);
	}

	/*
	 * getCourseAndStudentPaymentInfo
	 */

	public function testGetCourseAndStudentPaymentInfo_GroupDisabled()
	{
		$this->studentPaymentDbMock->method('getGroupRequiresStudentPayment')->willReturn(false);

		$studentPaymentStatus = $this->studentPayment->getCourseAndStudentPaymentInfo();

		$this->assertFalse($studentPaymentStatus->getCourseRequiresStudentPayment());
	}

	public function testGetCourseAndStudentPaymentInfo_CourseDisabled()
	{
		$this->studentPaymentDbMock->method('getCourseRequiresStudentPayment')->willReturn(false);

		$studentPaymentStatus = $this->studentPayment->getCourseAndStudentPaymentInfo();

		$this->assertFalse($studentPaymentStatus->getCourseRequiresStudentPayment());
	}

	public function testGetCourseAndStudentPaymentInfo_GroupAndCourseEnabled()
	{
		$this->studentPaymentDbMock->method('getGroupRequiresStudentPayment')->willReturn(true);
		$this->studentPaymentDbMock->method('getCourseRequiresStudentPayment')->willReturn(true);
		$this->studentPaymentDbMock->method('getStudentHasActivationCode')->willReturn(true);

		$studentPaymentStatus = $this->studentPayment->getCourseAndStudentPaymentInfo();

		$this->assertTrue($studentPaymentStatus->getStudentHasValidAccessCode());
	}

	/*
	 * getCoursePayStatusFromDatabase
	 */

	public function testGetCoursePayStatusFromDatabase_DbHasTrueValue()
	{
		$this->studentPaymentDbMock->method('getCourseRequiresStudentPayment')->willReturn(true);

		$studentPayStatus = $this->studentPayment->getCoursePayStatusFromDatabase(new StudentPayStatus());

		$this->assertTrue($studentPayStatus->getCourseRequiresStudentPayment());
	}

	public function testGetCoursePayStatusFromDatabase_DbHasFalseValue()
	{
		$this->studentPaymentDbMock->method('getCourseRequiresStudentPayment')->willReturn(false);

		$studentPayStatus = $this->studentPayment->getCoursePayStatusFromDatabase(new StudentPayStatus());

		$this->assertFalse($studentPayStatus->getCourseRequiresStudentPayment());
	}

	public function testGetCoursePayStatusFromDatabase_DbMissingValue()
	{
		$this->studentPaymentDbMock->method('getCourseRequiresStudentPayment')->willReturn(null);

		$studentPayStatus = $this->studentPayment->getCoursePayStatusFromDatabase(new StudentPayStatus());

		$this->assertFalse($studentPayStatus->getCourseRequiresStudentPayment());
	}

	/*
	 * getStudentPayStatusCacheFirst
	 */

	public function testGetStudentPayStatusCacheFirst()
	{
		$this->studentPaymentDbMock->method('getStudentHasActivationCode')->willReturn(true);

		$studentPayStatus = $this->studentPayment->getStudentPayStatusCacheFirst(new StudentPayStatus());

		$this->assertTrue($studentPayStatus->getStudentHasValidAccessCode());

	}

	public function testGetStudentPayStatusCacheFirst_DbMissingValue()
	{
		// Mock return data
		$apiResult = new StudentPayApiResult();
		$apiResult->setCourseRequiresStudentPayment(false);
		$apiResult->setStudentPaymentStatus(true);

		// Setup mocks
		$this->studentPaymentApiMock->method('getActivationStatusFromApi')->willReturn($apiResult);

		$this->studentPaymentDbMock->method('getStudentHasActivationCode')->willReturn(null);
		$this->studentPaymentDbMock->expects($this->once())->method('setStudentHasActivationCode')
			->with(true);

		// Run test
		$stupay = new StudentPayStatus();
		$stupay->setCourseRequiresStudentPayment(true); // this value should be returned to us unmodified.
		$studentPayStatus = $this->studentPayment->getStudentPayStatusCacheFirst($stupay);

		// Assertions
		$this->assertFalse($studentPayStatus->getCourseRequiresStudentPayment());
		$this->assertTrue($studentPayStatus->getStudentHasValidAccessCode());
	}

}

