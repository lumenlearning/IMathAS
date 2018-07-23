<?php

namespace OHM\Tests;

require_once(__DIR__ . '/../../../includes/sanitize.php');

use PHPUnit\Framework\TestCase;

use OHM\Mocks\PDOMock;
use OHM\Mocks\PDOStatementMock;

use OHM\Models\StudentPayApiResult;
use OHM\Includes\StudentPaymentDb;
use OHM\Includes\HttpRequest;
use OHM\Includes\StudentPaymentApi;
use OHM\Exceptions\StudentPaymentException;

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
	. '","section_requires_student_payment":true,"trial":{"expires_in":1234}}';
	const EVENT_LOGGED_OK_RESPONSE = '{"status": "ok"}';

	// Responses for institution data
	const INSTITUTION_RESPONSE = '{"id":"957c5216-7857-4b5a-9cb8-17c0c32bb608","name":"Hogwarts School of Witchcraft and Wizardry","external_ids":{"4":"43627281-b00b-4142-8e4c-1e435fe4f1c1","2204":"43627281-b00b-4142-8e4c-1e435fe4f1c1"},"bookstore_information":"Hello, world!","bookstore_url":"https://www.lumenlearning.com/"}';

	const CREATE_PAYMENT_SETTINGS_RESPONSE = '{"status": "ok"}';
	const ACCESS_TYPE_DIRECT_PAY_RESPONSE = '{"status":"ok","access_type":"' . StudentPayApiResult::ACCESS_TYPE_DIRECT_PAY . '"}';

	// The response we get from Lumenistration after relaying Stripe payment information.
	const PAYMENT_PROXY_SUCCESS_RESPONSE = '{"status":"ok","payment_info":{"id":6,"email":"michael@lumenlearning.com","charge_token":"ch_1CFWufLB7uSPM4hbXJx61Zqw","isbn":"9781640871632","last_four":"4242","section_id":null,"service_id":"43627281-b00b-4142-8e4c-1e435fe4f1c1","institution_id":"957c5216-7857-4b5a-9cb8-17c0c32bb608","created_at":"2018-04-11T00:34:13.986Z","updated_at":"2018-04-11T00:34:13.986Z","enrollment_id":"108"}}';

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
	 * paymentProxy
	 */

	function testPaymentProxy()
	{
		$this->curlMock->method('getInfo')->willReturn(201);
		$this->curlMock->method('execute')->willReturn(StudentPaymentApiTest::PAYMENT_PROXY_SUCCESS_RESPONSE);
		$this->curlMock->expects($this->once())->method('reset');

		$dataToSend = array(
			"institution_id" => "957c5216-7857-4b5a-9cb8-17c0c32bb608",
			"section_id" => "1",
			"enrollment_id" => "108",
			"stripeToken" => "tok_1bFdanLB3uSPM4hEP1vM2i9N",
			"stripeTokenType" => "card",
			"stripeEmail" => "michael@lumenlearning.com",
			"csrfp_token" => "5072b4d3ea"
		);

		$studentPayApiResult = $this->studentPaymentApi->paymentProxy($dataToSend);

		$this->assertEquals("ok", $studentPayApiResult->getStudentPaymentStatus());
		$this->assertEquals(6, $studentPayApiResult->getPaymentInfo()['id']);
		$this->assertEquals('4242', $studentPayApiResult->getPaymentInfo()['last_four']);
		$this->assertEquals('9781640871632', $studentPayApiResult->getPaymentInfo()['isbn']);
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
 	 * updateGroupPaymentSettings
 	 */

	function testUpdateGroupPaymentSettings()
	{
		$this->curlMock->method('getInfo')->willReturn(200);
		$this->curlMock->method('execute')->willReturn(StudentPaymentApiTest::CREATE_PAYMENT_SETTINGS_RESPONSE);
		$this->curlMock->expects($this->once())->method('reset');

		$studentPayApiResult = $this->studentPaymentApi->updateGroupPaymentSettings('direct_pay');

		$this->assertEquals("ok", $studentPayApiResult->getStudentPaymentStatus());
	}

	function testUpdateGroupPaymentSettings_Null()
	{
		$this->expectException(StudentPaymentException::class);

		$this->studentPaymentApi->updateGroupPaymentSettings(null);
	}

	function testUpdateGroupPaymentSettings_EmptyString()
	{
		$this->expectException(StudentPaymentException::class);

		$this->studentPaymentApi->updateGroupPaymentSettings('');
	}

	/*
	 * deleteGroupPaymentSettings
	 */

	function testDeleteGroupPaymentSettings()
	{
		$this->curlMock->method('getInfo')->willReturn(204);
		$this->curlMock->method('execute')->willReturn('');
		$this->curlMock->expects($this->once())->method('reset');

		$studentPayApiResult = $this->studentPaymentApi->deleteGroupPaymentSettings('not_required');

		// Don't really care what gets returned, as long as no exceptions are thrown.
		// (the API returns an HTTP 204 response)
		$this->assertNotNull($studentPayApiResult);
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

	function testParseApiResponse_AllValues()
	{
		$responseBody = '{'
			. '"status":"ok",'
			. '"message":"It\'s alllll gooooood!",'
			. '"trial": {"expires_in": "42"},'
			. '"access_type":"not_required",'
			. '"section_requires_student_payment":true,'
			. '"payment_info":{"id":11,"email":"michael@lumenlearning.com","charge_token":"ch_1CG9jELB7uSPM4hbHSZzlalh","isbn":"9781640871632","last_four":"4242","section_id":null,"service_id":"43627281-b00b-4142-8e4c-1e435fe4f1c1","institution_id":"bb968cf5-c4b1-44db-8618-dd3d128feba8","created_at":"2018-04-12T18:01:01.478Z","updated_at":"2018-04-12T18:01:01.478Z","enrollment_id":"108"},'
			. '"amount_cents":"3000",'
			. '"errors":["First error","Second error"],'
			. '"branding":{"logo_url":"https://www.google.com/image.png", "receipt_text":"To learn more, click here"}'
			. '}';

		$apiResult = $this->invokePrivateMethod($this->studentPaymentApi, 'parseApiResponse',
			array(200, $responseBody, array('200')));

		$this->assertEquals('ok', $apiResult->getStudentPaymentStatus());
		$this->assertEquals('It\'s alllll gooooood!', $apiResult->getApiUserMessage());
		$this->assertEquals('42', $apiResult->getTrialExpiresInSeconds());
		$this->assertEquals('not_required', $apiResult->getAccessType());
		$this->assertTrue($apiResult->getCourseRequiresStudentPayment());
		$this->assertEquals('3000', $apiResult->getPaymentAmountInCents());
		$this->assertEquals('First error', $apiResult->getErrors()[0]);
		$this->assertEquals('Second error', $apiResult->getErrors()[1]);
		$this->assertEquals('https://www.google.com/image.png', $apiResult->getSchoolLogoUrl());
		$this->assertEquals('To learn more, click here', $apiResult->getSchoolReceiptText());

		$this->assertNotNull($apiResult->getPaymentInfo());
		$this->assertEquals('ch_1CG9jELB7uSPM4hbHSZzlalh', $apiResult->getPaymentInfo()['charge_token']);
	}

	function testParseApiResponse_curlFailed()
	{
		$this->expectException(StudentPaymentException::class);

		$this->invokePrivateMethod($this->studentPaymentApi, 'parseApiResponse',
			array(0, null, array('200')));
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

	function testParseApiResponse_stripePaymentProxy()
	{
		$studentPayApiResult = $this->invokePrivateMethod($this->studentPaymentApi, 'parseApiResponse',
			array(200, StudentPaymentApiTest::PAYMENT_PROXY_SUCCESS_RESPONSE, array('200')));

		$this->assertEquals("ok", $studentPayApiResult->getStudentPaymentStatus());
		$this->assertEquals(6, $studentPayApiResult->getPaymentInfo()['id']);
		$this->assertEquals('4242', $studentPayApiResult->getPaymentInfo()['last_four']);
		$this->assertEquals('9781640871632', $studentPayApiResult->getPaymentInfo()['isbn']);
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
