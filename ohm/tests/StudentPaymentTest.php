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

	/*
	 * mapApiResultToPayStatus
	 */

	public function testMapApiResultToPayStatus_InTrial()
	{
		$this->studentPaymentDbMock->method('getCourseRequiresStudentPayment')->willReturn(true);

		$studentPayApiResult = new StudentPayApiResult();
		$studentPayApiResult->setCourseRequiresStudentPayment(true);
		$studentPayApiResult->setStudentPaymentStatus("in_trial");
		$studentPayApiResult->setTrialExpiresInSeconds(42);

		$studentPayStatus = $this->studentPayment->mapApiResultToPayStatus($studentPayApiResult, new StudentPayStatus());

		$this->assertEquals("in_trial", $studentPayStatus->getStudentPaymentRawStatus());
		$this->assertEquals(42, $studentPayStatus->getStudentTrialTimeRemainingSeconds());
		$this->assertTrue($studentPayStatus->getCourseRequiresStudentPayment());
		$this->assertTrue($studentPayStatus->getStudentIsInTrial());
		$this->assertFalse($studentPayStatus->getStudentHasValidAccessCode());
	}

	public function testMapApiResultToPayStatus_HasActivationCode()
	{
		$this->studentPaymentDbMock->method('getCourseRequiresStudentPayment')->willReturn(true);

		$studentPayApiResult = new StudentPayApiResult();
		$studentPayApiResult->setApiUserMessage("API user message");
		$studentPayApiResult->setCourseRequiresStudentPayment(true);
		$studentPayApiResult->setStudentPaymentStatus(StudentPayApiResult::IS_ACTIVATED);

		$studentPayStatus = $this->studentPayment->mapApiResultToPayStatus($studentPayApiResult, new StudentPayStatus());

		$this->assertEquals("API user message", $studentPayStatus->getUserMessage());
		$this->assertEquals(StudentPayApiResult::IS_ACTIVATED, $studentPayStatus->getStudentPaymentRawStatus());
		$this->assertNull($studentPayStatus->getStudentTrialTimeRemainingSeconds());
		$this->assertTrue($studentPayStatus->getCourseRequiresStudentPayment());
		$this->assertFalse($studentPayStatus->getStudentIsInTrial());
		$this->assertTrue($studentPayStatus->getStudentHasValidAccessCode());
	}

	public function testMapApiResultToPayStatus_Errors()
	{
		$this->studentPaymentDbMock->method('getCourseRequiresStudentPayment')->willReturn(true);

		$studentPayApiResult = new StudentPayApiResult();
		$studentPayApiResult->setApiUserMessage("API user message");
		$studentPayApiResult->setErrors(array('first error', 'second error'));

		$studentPayStatus = $this->studentPayment->mapApiResultToPayStatus($studentPayApiResult, new StudentPayStatus());

		$this->assertEquals('first error second error', $studentPayStatus->getUserMessage());
	}

	/*
	 * activateCode
	 */

	public function testActivateCode()
	{
		$studentPayApiResult = new StudentPayApiResult();
		$studentPayApiResult->setStudentPaymentStatus(StudentPayApiResult::ACTIVATION_SUCCESS);

		$this->studentPaymentApiMock->method('activateCode')->willReturn($studentPayApiResult);

		$studentPayStatus = $this->studentPayment->activateCode('asdf');

		$this->assertTrue($studentPayStatus->getStudentHasValidAccessCode());
		$this->assertEquals(StudentPayApiResult::ACTIVATION_SUCCESS,
			$studentPayStatus->getStudentPaymentRawStatus());
	}

	/*
	 * beginTrial
	 */

	public function testBeginTrial()
	{
		$studentPayApiResult = new StudentPayApiResult();
		$studentPayApiResult->setStudentPaymentStatus(StudentPayApiResult::START_TRIAL_SUCCESS);

		$this->studentPaymentApiMock->method('beginTrial')->willReturn($studentPayApiResult);

		$studentPayStatus = $this->studentPayment->beginTrial();

		$this->assertTrue($studentPayStatus->getStudentIsInTrial());
		$this->assertEquals(StudentPayApiResult::START_TRIAL_SUCCESS,
			$studentPayStatus->getStudentPaymentRawStatus());
	}

	/*
	 * extendTrial
	 */

	public function testExtendTrial()
	{
		$studentPayApiResult = new StudentPayApiResult();
		$studentPayApiResult->setStudentPaymentStatus(StudentPayApiResult::START_TRIAL_SUCCESS);

		$this->studentPaymentApiMock->method('beginTrial')->willReturn($studentPayApiResult);

		$studentPayStatus = $this->studentPayment->extendTrial();

		$this->assertTrue($studentPayStatus->getStudentIsInTrial());
		$this->assertEquals(StudentPayApiResult::START_TRIAL_SUCCESS,
			$studentPayStatus->getStudentPaymentRawStatus());
	}

	/*
	 * logTakeAssessmentDuringTrial
	 */

	public function testLogTakeAssessmentDuringTrial()
	{
		$studentPayApiResult = new StudentPayApiResult();
		$studentPayApiResult->setStudentPaymentStatus("ok");

		$this->studentPaymentApiMock->method('logTakeAssessmentDuringTrial')->willReturn($studentPayApiResult);

		$studentPayStatus = $this->studentPayment->logTakeAssessmentDuringTrial();

		$this->assertEquals("ok", $studentPayStatus->getStudentPaymentRawStatus());
	}

	public function testLogTakeAssessmentDuringTrial_Exception()
	{
		$studentPayApiResult = new StudentPayApiResult();
		$studentPayApiResult->setStudentPaymentStatus("dg94hnxkgu4hkd0e");

		$this->studentPaymentApiMock->method('logTakeAssessmentDuringTrial')
			->will($this->throwException(new StudentPaymentException('unit_test')));

		$studentPayStatus = $this->studentPayment->logTakeAssessmentDuringTrial();

		$this->assertNull($studentPayStatus);
	}

	/*
	 * logActivationPageSeen
	 */

	public function testLogActivationPageSeen()
	{
		$studentPayApiResult = new StudentPayApiResult();
		$studentPayApiResult->setStudentPaymentStatus("ok");

		$this->studentPaymentApiMock->method('logActivationPageSeen')->willReturn($studentPayApiResult);

		$studentPayStatus = $this->studentPayment->logActivationPageSeen();

		$this->assertEquals("ok", $studentPayStatus->getStudentPaymentRawStatus());
	}

	public function testLogActivationPageSeen_Exception()
	{
		$studentPayApiResult = new StudentPayApiResult();
		$studentPayApiResult->setStudentPaymentStatus("dg94hnxkgu4hkd0e");

		$this->studentPaymentApiMock->method('logActivationPageSeen')
			->will($this->throwException(new StudentPaymentException('unit_test')));

		$studentPayStatus = $this->studentPayment->logActivationPageSeen();

		$this->assertNull($studentPayStatus);
	}

}

