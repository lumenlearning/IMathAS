<?php

namespace OHM;

require_once(__DIR__ . '/../includes/StudentPaymentApi.php');
require_once(__DIR__ . '/../models/StudentPayStatus.php');
require_once(__DIR__ . '/../models/StudentPayApiResult.php');
require_once(__DIR__ . "/../../ohm/mocks/PDOMock.php");
require_once(__DIR__ . "/../../ohm/mocks/PDOStatementMock.php");

use PHPUnit\Framework\TestCase;

$GLOBALS['student_pay_api']['enabled'] = true;
$GLOBALS['student_pay_api']['base_url'] = 'http://127.0.0.1:5000/student_auth/v1';
$GLOBALS['student_pay_api']['timeout'] = 10;
$GLOBALS['student_pay_api']['jwt_secret'] = 'phptest_secret_goes_here';
$GLOBALS['student_pay_api']['debug'] = false;


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

	private $groupId = 128;
	private $courseID = 42;
	private $studentId = 3072;

	// Responses for course/student status.
	const ACTIVATION_SUCCESS_RESPONSE = '{"message":"You have successfully submitted your code.","status":"'
	. StudentPayApiResult::ACTIVATION_SUCCESS . '"}';
	const HAS_ACTIVATION_CODE_RESPONSE = '{"status":"' . StudentPayApiResult::IS_ACTIVATED
	. '","section_requires_student_payment":true,"trial_expired_in":1209422}';
	const NO_TRIAL_NO_ACTIVATION_RESPONSE =
		'{"section_requires_student_payment": true, "status": "' . StudentPayApiResult::NO_TRIAL_NO_ACTIVATION . '"}';
	const TRIAL_STARTED_RESPONSE = '{"status":"' . StudentPayApiResult::START_TRIAL_SUCCESS . '"}';
	const IN_TRIAL_RESPONSE = '{"status":"' . StudentPayApiResult::IN_TRIAL
	. '","section_requires_student_payment":true,"trial_expired_in":1234}';
	const EVENT_LOGGED_OK_RESPONSE = '{"status": "ok"}';

	// Responses for institution data
	const INSTITUTION_RESPONSE = '{"id":"957c5216-7857-4b5a-9cb8-17c0c32bb608","name":"Hogwarts School of Witchcraft and Wizardry","external_ids":{"4":"43627281-b00b-4142-8e4c-1e435fe4f1c1","2204":"43627281-b00b-4142-8e4c-1e435fe4f1c1"},"bookstore_information":"Hello, world!","bookstore_url":"https://www.lumenlearning.com/"}';

	const CREATE_PAYMENT_SETTINGS_RESPONSE = '{"status": "ok"}';
	const ACCESS_TYPE_DIRECT_PAY_RESPONSE = '{"status":"ok","access_type":"' . StudentPayApiResult::ACCESS_TYPE_DIRECT_PAY . '"}';

	const UNEXPECTED_RESPONSE = 'unexpected response text';
	const INVALID_CODE_RESPONSE = '{"message":"Code is not valid for this course section","status":"invalid_code_for_section"}';
	const INVALID_CODE_CHARACTERS_RESPONSE = '{"status":"invalid_code","errors":["Only numbers and letters are used in access codes. We also don\'t use confusing letters or numbers like l 1 0 o, etc."]}';


	function setUp()
	{
		$this->studentPaymentDbMock = $this->createMock(StudentPaymentDb::class);
		$this->curlMock = $this->createMock(HttpRequest::class);
		$this->pdoMock = $this->createMock(PDOMock::class);
		$this->pdoStatementMock = $this->createMock(PDOStatementMock::class);

		$this->studentPaymentApi = new StudentPaymentApi($this->groupId, $this->courseID,
			$this->studentId, $this->curlMock, $this->studentPaymentDbMock);
	}

	/**
	 * Call protected/private method of a class.
	 *
	 * @param object &$object Instantiated object that we will run method on.
	 * @param string $methodName Method name to call
	 * @param array $parameters Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 * @throws \ReflectionException
	 */
	public function invokePrivateMethod(&$object, $methodName, array $parameters = array())
	{
		$reflection = new \ReflectionClass(get_class($object));
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $parameters);
	}

	/*
	 * getActivationStatusFromApi
	 */

	function testGetActivationStatusFromApi_HasAccessCode()
	{
		$this->curlMock->method('getInfo')->willReturn(200);
		$this->curlMock->method('execute')->willReturn(StudentPaymentApiTest::HAS_ACTIVATION_CODE_RESPONSE);
		$this->curlMock->expects($this->once())->method('reset');
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetchColumn')->willReturn(1); // return an enrollment ID

		$studentPayApiResult = $this->studentPaymentApi->getActivationStatusFromApi(12);

		$this->assertTrue($studentPayApiResult->getCourseRequiresStudentPayment());
		$this->assertEquals(StudentPayApiResult::IS_ACTIVATED, $studentPayApiResult->getStudentPaymentStatus());
	}

	function testGetActivationStatusFromApi_NoTrialAndNoActivation()
	{
		$this->curlMock->method('getInfo')->willReturn(200);
		$this->curlMock->method('execute')->willReturn(StudentPaymentApiTest::NO_TRIAL_NO_ACTIVATION_RESPONSE);
		$this->curlMock->expects($this->once())->method('reset');
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetchColumn')->willReturn(1); // return an enrollment ID

		$studentPayApiResult = $this->studentPaymentApi->getActivationStatusFromApi(12);

		$this->assertTrue($studentPayApiResult->getCourseRequiresStudentPayment());
		$this->assertEquals(StudentPayApiResult::NO_TRIAL_NO_ACTIVATION,
			$studentPayApiResult->getStudentPaymentStatus());
	}

	function testGetActivationStatusFromApi_NoResponse()
	{
		$this->curlMock->method('getInfo')->willReturn(0);
		$this->curlMock->expects($this->once())->method('reset');
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);

		$this->expectException(StudentPaymentException::class);

		$this->studentPaymentApi->getActivationStatusFromApi(12);
	}

	function testGetActivationStatusFromApi_Non200Response()
	{
		$this->curlMock->method('getInfo')->willReturn(404);
		$this->curlMock->expects($this->once())->method('reset');
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);

		$this->expectException(StudentPaymentException::class);

		$this->studentPaymentApi->getActivationStatusFromApi(12);
	}

	function testGetActivationStatusFromApi_UnexpectedResponse()
	{
		$this->curlMock->method('getInfo')->willReturn(200);
		$this->curlMock->method('execute')->willReturn(StudentPaymentApiTest::UNEXPECTED_RESPONSE);
		$this->curlMock->expects($this->once())->method('reset');
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);

		$this->expectException(StudentPaymentException::class);

		$this->studentPaymentApi->getActivationStatusFromApi(12);
	}

	/*
	 * activateCode
	 */
	function testActivateCode()
	{
		$this->curlMock->method('getInfo')->willReturn(200);
		$this->curlMock->method('execute')->willReturn(StudentPaymentApiTest::ACTIVATION_SUCCESS_RESPONSE);
		$this->curlMock->expects($this->once())->method('reset');
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetchColumn')->willReturn(1); // return an enrollment ID

		$studentPayApiResult = $this->studentPaymentApi->getActivationStatusFromApi(12);

		$this->assertEquals(StudentPayApiResult::ACTIVATION_SUCCESS,
			$studentPayApiResult->getStudentPaymentStatus());
	}

	/*
	 * beginTrial
	 */

	function testBeginTrial()
	{
		$this->curlMock->method('getInfo')->willReturn(200);
		$this->curlMock->method('execute')->willReturn(StudentPaymentApiTest::TRIAL_STARTED_RESPONSE);
		$this->curlMock->expects($this->once())->method('reset');
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetchColumn')->willReturn(1); // return an enrollment ID

		$studentPayApiResult = $this->studentPaymentApi->getActivationStatusFromApi(12);

		$this->assertEquals(StudentPayApiResult::START_TRIAL_SUCCESS,
			$studentPayApiResult->getStudentPaymentStatus());
	}

	/*
	 * logEvent
	 */

	function testLogEvent()
	{
		$this->curlMock->method('getInfo')->willReturn(200);
		$this->curlMock->method('execute')->willReturn(StudentPaymentApiTest::EVENT_LOGGED_OK_RESPONSE);
		$this->curlMock->expects($this->once())->method('reset');
		$this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);
		$this->pdoStatementMock->method('fetchColumn')->willReturn(1); // return an enrollment ID

		$studentPayApiResult = $this->studentPaymentApi->logEvent('asdf');

		$this->assertEquals("ok", $studentPayApiResult->getStudentPaymentStatus());
	}

	function testLogEvent_MissingEventType_Null()
	{
		$this->curlMock->expects($this->once())->method('reset');

		$this->expectException(StudentPaymentException::class);

		$this->studentPaymentApi->logEvent(null);
	}

	function testLogEvent_MissingEventType_EmptyString()
	{
		$this->curlMock->expects($this->once())->method('reset');

		$this->expectException(StudentPaymentException::class);

		$this->studentPaymentApi->logEvent('');
	}

	/*
	 * getInstitutionData
	 */

	function testGetInstitutionData()
	{
		$this->curlMock->method('getInfo')->willReturn(200);
		$this->curlMock->method('execute')->willReturn(StudentPaymentApiTest::INSTITUTION_RESPONSE);
		$this->curlMock->expects($this->once())->method('reset');

		$result = $this->studentPaymentApi->getInstitutionData();

		$this->assertEquals('957c5216-7857-4b5a-9cb8-17c0c32bb608', $result->getId());
		$this->assertEquals('Hogwarts School of Witchcraft and Wizardry', $result->getName());
		$this->assertEquals('Hello, world!', $result->getBookstoreInformation());
		$this->assertEquals('https://www.lumenlearning.com/', $result->getBookstoreUrl());
		$this->assertEquals('43627281-b00b-4142-8e4c-1e435fe4f1c1', $result->getExternalIds()['4']);
		$this->assertEquals('43627281-b00b-4142-8e4c-1e435fe4f1c1', $result->getExternalIds()['2204']);
	}

	/*
 	 * createPaymentSettings
 	 */

	function testCreatePaymentSettings()
	{
		$this->curlMock->method('getInfo')->willReturn(200);
		$this->curlMock->method('execute')->willReturn(StudentPaymentApiTest::CREATE_PAYMENT_SETTINGS_RESPONSE);
		$this->curlMock->expects($this->once())->method('reset');

		$studentPayApiResult = $this->studentPaymentApi->createPaymentSettings('not_required');

		$this->assertEquals("ok", $studentPayApiResult->getStudentPaymentStatus());
	}

	function testCreatePaymentSettings_Null()
	{
		$this->expectException(StudentPaymentException::class);

		$this->studentPaymentApi->createPaymentSettings(null);
	}

	function testCreatePaymentSettings_EmptyString()
	{
		$this->expectException(StudentPaymentException::class);

		$this->studentPaymentApi->createPaymentSettings('');
	}

	/*
	 * getGroupAccessType
	 */

	function testGetGroupAccessType()
	{
		$this->curlMock->method('getInfo')->willReturn(200);
		$this->curlMock->method('execute')->willReturn(StudentPaymentApiTest::ACCESS_TYPE_DIRECT_PAY_RESPONSE);
		$this->curlMock->expects($this->once())->method('reset');

		$studentPayApiResult = $this->studentPaymentApi->getGroupAccessType();

		$this->assertEquals(StudentPayApiResult::ACCESS_TYPE_DIRECT_PAY,
			$studentPayApiResult->getAccessType());
	}

	/*
	 * parseApiResponse
	 */

	function testParseApiResponse_curlFailed()
	{
		$this->expectException(StudentPaymentException::class);

		$this->invokePrivateMethod($this->studentPaymentApi, 'parseApiResponse',
			array(0, null, array('200')));
	}

	function testParseApiResponse_nullResponse()
	{
		$this->expectException(StudentPaymentException::class);

		$this->invokePrivateMethod($this->studentPaymentApi, 'parseApiResponse',
			array(200, null, array('200')));
	}

	function testParseApiResponse_emptyResponse()
	{
		$this->expectException(StudentPaymentException::class);

		$this->invokePrivateMethod($this->studentPaymentApi, 'parseApiResponse',
			array(200, '', array('200')));
	}

	function testParseApiResponse_missingStatus()
	{
		$this->expectException(StudentPaymentException::class);

		$this->invokePrivateMethod($this->studentPaymentApi, 'parseApiResponse',
			array(200, '{}', array('200')));
	}

	function testParseApiResponse_notPaid_and_notInTrial()
	{
		$studentPayApiResult = $this->invokePrivateMethod($this->studentPaymentApi, 'parseApiResponse',
			array(200, StudentPaymentApiTest::NO_TRIAL_NO_ACTIVATION_RESPONSE, array('200')));

		$this->assertTrue($studentPayApiResult->getCourseRequiresStudentPayment());
		$this->assertEquals(StudentPayApiResult::NO_TRIAL_NO_ACTIVATION,
			$studentPayApiResult->getStudentPaymentStatus());
	}

	function testParseApiResponse_beginTrial()
	{
		$studentPayApiResult = $this->invokePrivateMethod($this->studentPaymentApi, 'parseApiResponse',
			array(200, StudentPaymentApiTest::TRIAL_STARTED_RESPONSE, array('200')));

		$this->assertEquals(StudentPayApiResult::START_TRIAL_SUCCESS,
			$studentPayApiResult->getStudentPaymentStatus());
	}

	function testParseApiResponse_inTrial()
	{
		$studentPayApiResult = $this->invokePrivateMethod($this->studentPaymentApi, 'parseApiResponse',
			array(200, StudentPaymentApiTest::IN_TRIAL_RESPONSE, array('200')));

		$this->assertTrue($studentPayApiResult->getCourseRequiresStudentPayment());
		$this->assertEquals(1234, $studentPayApiResult->getTrialExpiresInSeconds());
		$this->assertEquals(StudentPayApiResult::IN_TRIAL, $studentPayApiResult->getStudentPaymentStatus());
	}

	function testParseApiResponse_extendTrial()
	{
		$studentPayApiResult = $this->invokePrivateMethod($this->studentPaymentApi, 'parseApiResponse',
			array(200, StudentPaymentApiTest::TRIAL_STARTED_RESPONSE, array('200')));

		$this->assertEquals(StudentPayApiResult::START_TRIAL_SUCCESS,
			$studentPayApiResult->getStudentPaymentStatus());
	}

	function testParseApiResponse_hasAccessCode()
	{
		$studentPayApiResult = $this->invokePrivateMethod($this->studentPaymentApi, 'parseApiResponse',
			array(200, StudentPaymentApiTest::HAS_ACTIVATION_CODE_RESPONSE, array('200')));

		$this->assertTrue($studentPayApiResult->getCourseRequiresStudentPayment());
		$this->assertEquals(StudentPayApiResult::IS_ACTIVATED, $studentPayApiResult->getStudentPaymentStatus());
	}

	function testParseApiResponse_sendEnrollmentEvent()
	{
		$studentPayApiResult = $this->invokePrivateMethod($this->studentPaymentApi, 'parseApiResponse',
			array(200, StudentPaymentApiTest::EVENT_LOGGED_OK_RESPONSE, array('200')));

		$this->assertEquals("ok", $studentPayApiResult->getStudentPaymentStatus());
	}

	function testParseApiResponse_invalidAccessCode()
	{
		$studentPayApiResult = $this->invokePrivateMethod($this->studentPaymentApi, 'parseApiResponse',
			array(404, StudentPaymentApiTest::INVALID_CODE_RESPONSE, array('200')));

		$this->assertEquals("Code is not valid for this course section",
			$studentPayApiResult->getApiUserMessage());
	}

	function testParseApiResponse_invalidAccessCodeCharacters()
	{
		$studentPayApiResult = $this->invokePrivateMethod($this->studentPaymentApi, 'parseApiResponse',
			array(400, StudentPaymentApiTest::INVALID_CODE_CHARACTERS_RESPONSE, array('200')));

		$this->assertEquals("Only numbers and letters are used in access codes."
			. " We also don't use confusing letters or numbers like l 1 0 o, etc.",
			$studentPayApiResult->getErrors()[0]);
	}

	/*
	 * parseInstitutionResponse
	 */

	function testParseInstitutionResponse_curlFailed()
	{
		$this->expectException(StudentPaymentException::class);

		$this->invokePrivateMethod($this->studentPaymentApi, 'parseInstitutionResponse',
			array(0, null, array('200')));
	}

	function testParseInstitutionResponse_nullResponse()
	{
		$this->expectException(StudentPaymentException::class);

		$this->invokePrivateMethod($this->studentPaymentApi, 'parseInstitutionResponse',
			array(200, null, array('200')));
	}

	function testParseInstitutionResponse_emptyResponse()
	{
		$this->expectException(StudentPaymentException::class);

		$this->invokePrivateMethod($this->studentPaymentApi, 'parseInstitutionResponse',
			array(200, '', array('200')));
	}

	function testParseInstitutionResponse_missingId()
	{
		$this->expectException(StudentPaymentException::class);

		$this->invokePrivateMethod($this->studentPaymentApi, 'parseInstitutionResponse',
			array(200, '{}', array('200')));
	}

	function testParseInstitutionResponse()
	{
		$result = $this->invokePrivateMethod($this->studentPaymentApi, 'parseInstitutionResponse',
			array(200, StudentPaymentApiTest::INSTITUTION_RESPONSE, array('200')));

		$this->assertEquals('957c5216-7857-4b5a-9cb8-17c0c32bb608', $result->getId());
		$this->assertEquals('Hogwarts School of Witchcraft and Wizardry', $result->getName());
		$this->assertEquals('Hello, world!', $result->getBookstoreInformation());
		$this->assertEquals('https://www.lumenlearning.com/', $result->getBookstoreUrl());
		$this->assertEquals('43627281-b00b-4142-8e4c-1e435fe4f1c1', $result->getExternalIds()['4']);
		$this->assertEquals('43627281-b00b-4142-8e4c-1e435fe4f1c1', $result->getExternalIds()['2204']);
	}

	/*
	 * getInstitutionId
	 */

	function testGetInstitutionIdForApi()
	{
		$lumenGuid = '2826e8e4-8d79-4c45-a8d6-0ef862496bc1';
		$this->studentPaymentDbMock->method('getLumenGuid')->willReturn($lumenGuid);

		$result = $this->invokePrivateMethod($this->studentPaymentApi, 'getInstitutionIdForApi');

		$this->assertEquals($lumenGuid, $result);
	}

	function testGetInstitutionIdForApi_NullGuid()
	{
		$this->studentPaymentDbMock->method('getLumenGuid')->willReturn(null);

		$result = $this->invokePrivateMethod($this->studentPaymentApi, 'getInstitutionIdForApi');

		$this->assertEquals($this->groupId, $result);
	}

	function testGetInstitutionIdForApi_EmptyGuid()
	{
		$this->studentPaymentDbMock->method('getLumenGuid')->willReturn('');

		$result = $this->invokePrivateMethod($this->studentPaymentApi, 'getInstitutionIdForApi');

		$this->assertEquals($this->groupId, $result);
	}

}
