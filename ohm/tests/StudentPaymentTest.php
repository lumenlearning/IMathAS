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
	 * getCoursePayStatusCacheFirst
	 */

	public function testGetCoursePayStatusCacheFirst_DbHasValue()
	{
		$this->studentPaymentDbMock->method('getCourseRequiresStudentPayment')->willReturn(true);

		$studentPayStatus = $this->studentPayment->getCoursePayStatusCacheFirst(new StudentPayStatus());

		$this->assertTrue($studentPayStatus->getCourseRequiresStudentPayment());
	}

	public function testGetCoursePayStatusCacheFirst_DbMissingValue1()
	{
		// Test data
		$apiResult = new StudentPayApiResult();
		$apiResult->setCourseRequiresStudentPayment(true);
		$apiResult->setStudentPaymentStatus(true);

		// Setup mocks
		$this->studentPaymentApiMock->method('getActivationStatusFromApi')->willReturn($apiResult);

		$this->studentPaymentDbMock->method('getCourseRequiresStudentPayment')->willReturn(null);
		$this->studentPaymentDbMock->expects($this->once())->method('setCourseRequiresStudentPayment')
			->with(true);
		// This should be called if the student has a valid access code.
		$this->studentPaymentDbMock->expects($this->once())->method('setStudentHasActivationCode')
			->with(true);

		// Run test
		$studentPayStatus = $this->studentPayment->getCoursePayStatusCacheFirst(new StudentPayStatus());

		// Assertions
		$this->assertTrue($studentPayStatus->getCourseRequiresStudentPayment());
		$this->assertTrue($studentPayStatus->getStudentHasValidAccessCode());
	}

	public function testGetCoursePayStatusCacheFirst_DbMissingValue2()
	{
		// Test data
		$apiResult = new StudentPayApiResult();
		$apiResult->setCourseRequiresStudentPayment(true);
		$apiResult->setStudentPaymentStatus(false);

		// Setup mocks
		$this->studentPaymentApiMock->method('getActivationStatusFromApi')->willReturn($apiResult);

		$this->studentPaymentDbMock->method('getCourseRequiresStudentPayment')->willReturn(null);
		$this->studentPaymentDbMock->expects($this->once())->method('setCourseRequiresStudentPayment')
			->with(true);
		// This should NOT be called if the student does NOT have a valid access code.
		$this->studentPaymentDbMock->expects($this->never())->method('setStudentHasActivationCode');

		// Run test
		$studentPayStatus = $this->studentPayment->getCoursePayStatusCacheFirst(new StudentPayStatus());

		// Assertions
		$this->assertTrue($studentPayStatus->getCourseRequiresStudentPayment());
		$this->assertFalse($studentPayStatus->getStudentHasValidAccessCode());
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

	public function testGetStudentPayStatusCacheFirst_DbMissingValue1()
	{
		// Mock return data
		$apiResult = new StudentPayApiResult();
		$apiResult->setCourseRequiresStudentPayment(true);
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
		$this->assertTrue($studentPayStatus->getCourseRequiresStudentPayment());
		$this->assertTrue($studentPayStatus->getStudentHasValidAccessCode());
	}

}

