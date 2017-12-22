<?php

namespace OHM;

require_once(__DIR__ . "/../../includes/sanitize.php");
require_once(__DIR__ . "/../exceptions/StudentPaymentException.php");
require_once(__DIR__ . "/../models/StudentPayApiResult.php");
require_once(__DIR__ . "/../models/LumenistrationInstitution.php");
require_once(__DIR__ . "/StudentPaymentDb.php");
require_once(__DIR__ . "/CurlRequest.php");

/**
 * Class StudentPaymentApi - Handle all direct interaction with the student payment API.
 *
 * In most cases, this class is not used directly. Instead, the StudentPayment class is
 * used so we can cache/persist state in the database to reduce the number of API calls
 * made and record metrics.
 *
 * Note: IDs are currently sent in API requests as strings, as required by the API.
 *
 * @see StudentPayment For a higher level abstraction with database caching / persistence.
 *
 * @package OHM
 */
class StudentPaymentApi
{

	private $curl;
	private $studentPaymentDb;

	private $groupId;
	private $courseId;
	private $studentId;

	/**
	 * StudentPayment constructor.
	 * @param $groupId integer The student's group ID. (MySQL imas_groups. ID column)
	 * @param $courseId integer The course ID. (MySQM imas_courses, ID column)
	 * @param $studentId integer The student's user ID. (MySQL imas_users table, ID column)
	 * @param $curl HttpRequest An implementation of HttpRequest. (optional; for unit testing)
	 * @param $studentPaymentDb StudentPaymentDb An implementation of StudentPaymentDb. (optional; for unit testing)
	 */
	public function __construct($groupId, $courseId, $studentId, $curl = null, $studentPaymentDb = null)
	{
		$this->groupId = $groupId;
		$this->courseId = $courseId;
		$this->studentId = $studentId;
		$this->curl = $curl;
		$this->studentPaymentDb = $studentPaymentDb;

		if (null == $curl) {
			$this->curl = new CurlRequest();
		}

		if (null == $studentPaymentDb) {
			$this->studentPaymentDb = new StudentPaymentDb($groupId, $courseId, $studentId);
		}
	}

	/**
	 * Log a debugging message, if debugging for student payments is enabled.
	 * @param $message string The debug message to log.
	 */
	private function debug($message)
	{
		if ($GLOBALS['student_pay_api']['debug']) {
			error_log($message);
		}
	}

	/**
	 * Get the activation status for a student's enrollment in a course.
	 *
	 * @return StudentPayApiResult A StudentPayApiResult object.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function getActivationStatusFromApi()
	{
		$this->curl->reset();

		$enrollmentId = $this->studentPaymentDb->getStudentEnrollmentId();

		$requestUrl = $GLOBALS['student_pay_api']['base_url'] . '/student_pay?' .
			\Sanitize::generateQueryStringFromMap(array(
				'institution_id' => "$this->groupId",
				'section_id' => "$this->courseId",
				'enrollment_id' => "$enrollmentId"
			));
		$this->debug("Student API URL = " . $requestUrl);
		$this->curl->setUrl($requestUrl);

		$headers = array(
			'Authorization: Bearer ' . $GLOBALS['student_pay_api']['jwt_secret'],
			'Accept: application/json'
		);
		$this->curl->setOption(CURLOPT_HTTPHEADER, $headers);
		$this->curl->setOption(CURLOPT_TIMEOUT, $GLOBALS['student_pay_api']['timeout']);
		$this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
		$result = $this->curl->execute();
		$status = $this->curl->getInfo(CURLINFO_HTTP_CODE);

		$studentPayApiResult = $this->parseApiResponse($status, $result, [200]);
		$this->curl->close();

		return $studentPayApiResult;
	}

	/**
	 * Activate a code.
	 *
	 * @param $activationCode string The activation code.
	 * @return StudentPayApiResult An instance of StudentPayApiResult.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function activateCode($activationCode)
	{
		$this->curl->reset();

		$enrollmentId = $this->studentPaymentDb->getStudentEnrollmentId();

		$requestUrl = $GLOBALS['student_pay_api']['base_url'] . '/student_pay';
		$this->debug("Student API URL = " . $requestUrl);
		$this->curl->setUrl($requestUrl);

		$requestData = json_encode(array(
			'institution_id' => "$this->groupId",
			'section_id' => "$this->courseId",
			'enrollment_id' => "$enrollmentId",
			'code' => $activationCode
		));
		$this->debug("Sending content: " . $requestData);

		$headers = array(
			'Authorization: Bearer ' . $GLOBALS['student_pay_api']['jwt_secret'],
			'Accept: application/json',
			'Content-Type: application/json',
		);
		$this->curl->setOption(CURLOPT_HTTPHEADER, $headers);
		$this->curl->setOption(CURLOPT_TIMEOUT, $GLOBALS['student_pay_api']['timeout']);
		$this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
		$this->curl->setOption(CURLOPT_POSTFIELDS, $requestData);
		$result = $this->curl->execute();
		$status = $this->curl->getInfo(CURLINFO_HTTP_CODE);

		$studentPayApiResult = $this->parseApiResponse($status, $result, [200, 204]);
		$this->curl->close();

		return $studentPayApiResult;
	}

	/**
	 * Update an existing activation to begin a trial.
	 *
	 * @return StudentPayApiResult An instance of StudentPayApiResult.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function beginTrial()
	{
		$this->curl->reset();

		$enrollmentId = $this->studentPaymentDb->getStudentEnrollmentId();

		$requestUrl = $GLOBALS['student_pay_api']['base_url'] . '/student_pay/trials';
		$this->debug("Student API URL = " . $requestUrl);
		$this->curl->setUrl($requestUrl);

		$requestData = json_encode(array(
			'institution_id' => "$this->groupId",
			'section_id' => "$this->courseId",
			'enrollment_id' => "$enrollmentId"
		));
		$this->debug("Sending content: " . $requestData);

		$headers = array(
			'Authorization: Bearer ' . $GLOBALS['student_pay_api']['jwt_secret'],
			'Accept: application/json',
			'Content-Type: application/json',
		);
		$this->curl->setOption(CURLOPT_HTTPHEADER, $headers);
		$this->curl->setOption(CURLOPT_TIMEOUT, $GLOBALS['student_pay_api']['timeout']);
		$this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
		$this->curl->setOption(CURLOPT_POSTFIELDS, $requestData);
		$result = $this->curl->execute();
		$status = $this->curl->getInfo(CURLINFO_HTTP_CODE);

		$studentPayApiResult = $this->parseApiResponse($status, $result, [200, 204]);
		$this->curl->close();

		return $studentPayApiResult;
	}

	/**
	 * Notify the student payment API that a student is taking an assessment while under trial, for metrics.
	 *
	 * @return StudentPayApiResult An instance of StudentPayApiResult.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function logTakeAssessmentDuringTrial()
	{
		return $this->logEvent('free_quiz_started');
	}

	/**
	 * Notify the student payment API that a student has seen the activation code page, for metrics.
	 *
	 * @return StudentPayApiResult An instance of StudentPayApiResult.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function logActivationPageSeen()
	{
		return $this->logEvent('saw_activation_code_page');
	}

	/**
	 * Notify the student payment API that a student has declined a trial, for metrics.
	 *
	 * @return StudentPayApiResult An instance of StudentPayApiResult.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function logDeclineTrial()
	{
		return $this->logEvent('refused_trial_start');
	}

	/**
	 * Notify the student payment API of an event, for metrics.
	 *
	 * @param $eventType string The raw event type string to send.
	 * @return StudentPayApiResult An instance of StudentPayApiResult.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function logEvent($eventType)
	{
		$this->curl->reset();

		if (empty($eventType)) {
			throw new StudentPaymentException("No event type was specified.");
		}

		$enrollmentId = $this->studentPaymentDb->getStudentEnrollmentId();

		$requestUrl = $GLOBALS['student_pay_api']['base_url'] . '/enrollment_events';
		$this->debug("Student API URL = " . $requestUrl);
		$this->curl->setUrl($requestUrl);

		$requestData = json_encode(array(
			'event_type' => "$eventType", // Quoted to ensure the value is always sent as a string.
			'institution_id' => "$this->groupId",
			'section_id' => "$this->courseId",
			'enrollment_id' => "$enrollmentId"
		));
		$this->debug("Sending content: " . $requestData);

		$headers = array(
			'Authorization: Bearer ' . $GLOBALS['student_pay_api']['jwt_secret'],
			'Accept: application/json',
			'Content-Type: application/json',
		);
		$this->curl->setOption(CURLOPT_HTTPHEADER, $headers);
		$this->curl->setOption(CURLOPT_TIMEOUT, $GLOBALS['student_pay_api']['timeout']);
		$this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
		$this->curl->setOption(CURLOPT_POSTFIELDS, $requestData);
		$result = $this->curl->execute();
		$status = $this->curl->getInfo(CURLINFO_HTTP_CODE);

		$studentPayApiResult = $this->parseApiResponse($status, $result, [200, 204]);
		$this->curl->close();

		return $studentPayApiResult;
	}

	/**
	 * Get institution data for the group by its ID. (ID specified in class constructor)
	 *
	 * @return LumenistrationInstitution An instance of LumenistrationInstitution.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function getInstitutionData()
	{
		$this->curl->reset();

		$requestUrl = $GLOBALS['student_pay_api']['base_url'] . '/institutions/' . $this->groupId;
		$this->debug("Lumenistration API URL = " . $requestUrl);
		$this->curl->setUrl($requestUrl);

		$headers = array(
			'Authorization: Bearer ' . $GLOBALS['student_pay_api']['jwt_secret'],
			'Accept: application/json',
		);
		$this->curl->setOption(CURLOPT_HTTPHEADER, $headers);
		$this->curl->setOption(CURLOPT_TIMEOUT, $GLOBALS['student_pay_api']['timeout']);
		$this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
		$result = $this->curl->execute();
		$status = $this->curl->getInfo(CURLINFO_HTTP_CODE);

		$lumenistrationInstitution = $this->parseInstitutionResponse($status, $result, [200]);
		$this->curl->close();

		return $lumenistrationInstitution;
	}

	/**
	 * Parse an API response containing course and/or student data.
	 *
	 * @param $status integer The HTTP status code received from the API.
	 * @param $responseBody string The raw response body.
	 * @param $acceptableHttpStatusList mixed An array of acceptable integer status codes.
	 * @return StudentPayApiResult An instance of StudentPayApiResult.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	private function parseApiResponse($status, $responseBody, $acceptableHttpStatusList)
	{
		$this->debug(sprintf("Student payment API HTTP status %d. Raw response: %s", $status, $responseBody));

		$apiResponse = json_decode($responseBody, true);

		$acceptable4xxError = false;
		if (0 == $status) {
			// curl returns 0 on http failure
			throw new StudentPaymentException("Unable to connect to student payment API.");
		}
		if (null == $apiResponse || '' == $apiResponse) {
			// json_decode failed to find valid json content
			throw new StudentPaymentException("Unexpected content returned from student payment API: "
				. $responseBody);
		}
		if (404 == $status) {
			// Currently, we only accept a 404 status for invalid activation code responses.
			if (!isset($apiResponse['status']) || "invalid_code_for_section" != $apiResponse['status']) {
				throw new StudentPaymentException(sprintf(
					"Unexpected HTTP status %d returned from student payment API. Content: %s", $status,
					$responseBody));
			}
			$acceptable4xxError = true;
		}
		if (400 == $status) {
			// Currently, we only accept a 400 status for invalid activation code responses.
			if (!isset($apiResponse['status']) || !isset($apiResponse['errors'])) {
				throw new StudentPaymentException(sprintf(
					"Unexpected HTTP status %d returned from student payment API. Content: %s", $status,
					$responseBody));
			}
			$acceptable4xxError = true;
		}
		if (!$acceptable4xxError && !in_array($status, $acceptableHttpStatusList)) {
			throw new StudentPaymentException(sprintf(
				"Unexpected HTTP status %d returned from student payment API. Content: %s", $status,
				$responseBody));
		}
		if (!isset($apiResponse['status'])) {
			// All endpoints should return a status in the json payload.
			throw new StudentPaymentException(sprintf(
				"Student payment API did not return a status in JSON payload. HTTP status: %s, Content: %s",
				$status, $responseBody));
		}

		$studentPayApiResult = new StudentPayApiResult();
		$studentPayApiResult->setStudentPaymentStatus($apiResponse['status']);
		if (isset($apiResponse['message'])) {
			$studentPayApiResult->setApiUserMessage($apiResponse['message']);
		}
		if (isset($apiResponse['trial_expired_in'])) {
			$studentPayApiResult->setTrialExpiresInSeconds($apiResponse['trial_expired_in']);
		}
		if (isset($apiResponse['section_requires_student_payment'])) {
			$studentPayApiResult->setCourseRequiresStudentPayment($apiResponse['section_requires_student_payment']);
		}
		if (isset($apiResponse['errors'])) {
			$studentPayApiResult->setErrors($apiResponse['errors']);
		}

		return $studentPayApiResult;
	}

	/**
	 * Parse an API response containing Institution data.
	 *
	 * @param $status integer The HTTP status code received from the API.
	 * @param $responseBody string The raw response body.
	 * @param $acceptableHttpStatusList mixed An array of acceptable integer status codes.
	 * @return LumenistrationInstitution An instance of LumenistrationInstitution.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	private function parseInstitutionResponse($status, $responseBody, $acceptableHttpStatusList)
	{
		$this->debug(sprintf("Student payment API HTTP status %d. Raw response: %s", $status, $responseBody));

		$apiResponse = json_decode($responseBody, true);

		if (0 == $status) {
			// curl returns 0 on http failure
			throw new StudentPaymentException("Unable to connect to student payment API.");
		}
		if (null == $apiResponse || '' == $apiResponse) {
			// json_decode failed to find valid json content
			throw new StudentPaymentException("Unexpected content returned from student payment API: "
				. $responseBody);
		}
		if (!in_array($status, $acceptableHttpStatusList)) {
			throw new StudentPaymentException(sprintf(
				"Unexpected HTTP status %d returned from student payment API. Content: %s", $status,
				$responseBody));
		}
		if (!isset($apiResponse['name'])) {
			// All endpoints should return a status in the json payload.
			throw new StudentPaymentException(sprintf(
				"Student payment API did not return an institution name in JSON payload. HTTP Status: %s, Content: %s",
				$status, $responseBody));
		}

		$lumenistrationInstitution = new LumenistrationInstitution();
		$lumenistrationInstitution->setId($apiResponse['id']);
		$lumenistrationInstitution->setName($apiResponse['name']);
		$lumenistrationInstitution->setBookstoreInformation($apiResponse['bookstore_information']);
		$lumenistrationInstitution->setBookstoreUrl($apiResponse['bookstore_url']);

		$allExternalIds = array();
		foreach ($apiResponse['external_ids'] as $key => $value) {
			$allExternalIds["$key"] = "$value"; // enclosed in quotes to force string type
		}
		$lumenistrationInstitution->setExternalIds($allExternalIds);

		return $lumenistrationInstitution;
	}

}
