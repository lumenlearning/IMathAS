<?php

namespace OHM;

require_once(__DIR__ . '/../includes/StudentPaymentApi.php');
require_once(__DIR__ . '/../models/StudentPayStatus.php');
require_once(__DIR__ . "/../../ohm/mocks/PDOMock.php");
require_once(__DIR__ . "/../../ohm/mocks/PDOStatementMock.php");

use PHPUnit\Framework\TestCase;

$GLOBALS['student_pay_api']['enabled'] = true;
$GLOBALS['student_pay_api']['base_url'] = 'http://127.0.0.1:5000/student_auth/v1';
$GLOBALS['student_pay_api']['timeout'] = 10;
$GLOBALS['student_pay_api']['jwt_secret'] = 'phptest_secret_goes_here';


/**
 * @covers StudentPaymentApi
 */
final class StudentPaymentApiTest extends TestCase
{

	private $studentPaymentApi;

	private $studentPaymentDbMock;
	private $curlMock;
	private $pdoMock;
	private $pdoStatementMock;

	private static $paidResponse =
		'{"course_requires_student_payment": true, "student_status": "' . StudentPayStatus::PAID . '"}';
	private static $notPaidResponse =
		'{"course_requires_student_payment": true, "student_status": "' . StudentPayStatus::NOT_PAID . '"}';
	private static $unexpectedResponse = 'unexpected response text';


	function setUp()
	{
		$this->studentPaymentDbMock = $this->createMock(StudentPaymentDb::class);
		$this->curlMock = $this->createMock(HttpRequest::class);
		$this->pdoMock = $this->createMock(PDOMock::class);
		$this->pdoStatementMock = $this->createMock(PDOStatementMock::class);

		$this->studentPaymentApi = new StudentPaymentApi(128, 42, 3072);
		$this->studentPaymentApi->setCurl($this->curlMock);
		$this->studentPaymentApi->setStudentPaymentDb($this->studentPaymentDbMock);
	}

	/*
	 * getActivationStatusFromApi
	 */

	function testGetActivationStatusFromApi_Paid()
	{
		$this->curlMock->method('getInfo')->willReturn(200);
		$this->curlMock->method('execute')->willReturn($this::$paidResponse);
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetchColumn')->willReturn(1); // return an enrollment ID

		$studentPayApiResult = $this->studentPaymentApi->getActivationStatusFromApi(12);

		$this->assertTrue($studentPayApiResult->getCourseRequiresStudentPayment());
		$this->assertEquals(StudentPayStatus::PAID, $studentPayApiResult->getStudentPaymentStatus());
	}

	function testGetActivationStatusFromApi_NotPaid()
	{
		$this->curlMock->method('getInfo')->willReturn(200);
		$this->curlMock->method('execute')->willReturn($this::$notPaidResponse);
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetchColumn')->willReturn(1); // return an enrollment ID

		$studentPayApiResult = $this->studentPaymentApi->getActivationStatusFromApi(12);

		$this->assertTrue($studentPayApiResult->getCourseRequiresStudentPayment());
		$this->assertEquals(StudentPayStatus::NOT_PAID, $studentPayApiResult->getStudentPaymentStatus());
	}

	function testGetActivationStatusFromApi_NoResponse()
	{
		$this->curlMock->method('getInfo')->willReturn(0);
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);

		$this->expectException(\Exception::class);

		$this->studentPaymentApi->getActivationStatusFromApi(12);
	}

	function testGetActivationStatusFromApi_Non200Response()
	{
		$this->curlMock->method('getInfo')->willReturn(404);
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);

		$this->expectException(\Exception::class);

		$this->studentPaymentApi->getActivationStatusFromApi(12);
	}

	function testGetActivationStatusFromApi_UnexpectedResponse()
	{
		$this->curlMock->method('getInfo')->willReturn(200);
		$this->curlMock->method('execute')->willReturn($this::$unexpectedResponse);
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);

		$this->expectException(\Exception::class);

		$this->studentPaymentApi->getActivationStatusFromApi(12);
	}

	/*
	 * activateCode
	 */

	/*
	 * updateActivation
	 */

}