<?php

namespace OHM\Includes;

use OHM\Models\LumenistrationInstitution;
use OHM\Models\StudentPayApiResult;
use OHM\Exceptions\StudentPaymentException;

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
 * @see https://github.com/lumenlearning/lumenistration/blob/master/docs/API.md
 *
 * @package OHM
 */
class StudentPaymentApi
{

	private $curl;
	private $studentPaymentDb;
	private $institutionIdForApi = null;

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
				'institution_id' => (string)$this->getInstitutionIdForApi(),
				'section_id' => "$this->courseId",
				'enrollment_id' => "$enrollmentId"
			));
		$this->debug("StudentPaymentApi->getActivationStatusFromApi : GET " . $requestUrl);
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
		$this->debug("StudentPaymentApi->activateCode : POST " . $requestUrl);
		$this->curl->setUrl($requestUrl);

		$requestData = json_encode(array(
			'institution_id' => (string)$this->getInstitutionIdForApi(),
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

		$studentPayApiResult = $this->parseApiResponse($status, $result, [200, 204, 404, 400]);
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
		$this->debug("StudentPaymentApi->beginTrial : POST " . $requestUrl);
		$this->curl->setUrl($requestUrl);

		$requestData = json_encode(array(
			'institution_id' => (string)$this->getInstitutionIdForApi(),
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
	 * Pass a JSON object directly to the student payment API.
	 *
	 * @param $data array The data to pass to the student payment API.
	 * @return StudentPayApiResult An instance of StudentPayApiResult.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function paymentProxy($data)
	{
		$this->curl->reset();

		$enrollmentId = $this->studentPaymentDb->getStudentEnrollmentId();

		$requestUrl = $GLOBALS['student_pay_api']['base_url'] . '/student_pay';
		$this->debug("StudentPaymentApi->paymentProxy : POST " . $requestUrl);
		$this->curl->setUrl($requestUrl);

		$requestData = json_encode(array_merge(
			array(
				'institution_id' => (string)$this->getInstitutionIdForApi(),
				'section_id' => "$this->courseId",
				'enrollment_id' => "$enrollmentId",
				'token' => $data['stripeToken'],
			),
			$data
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

		$studentPayApiResult = $this->parseApiResponse($status, $result, [200, 201]);
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
	 * Notify the student payment API that a student has seen the activation code page, for metrics.
	 *
	 * @return StudentPayApiResult An instance of StudentPayApiResult.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function logDirectPaymentPageSeen()
	{
		return $this->logEvent('saw_direct_payment_page');
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
		$this->debug("StudentPaymentApi->logEvent : POST " . $requestUrl);
		$this->curl->setUrl($requestUrl);

		$requestData = json_encode(array(
			'event_type' => "$eventType", // Quoted to ensure the value is always sent as a string.
			'institution_id' => (string)$this->getInstitutionIdForApi(),
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

		$requestUrl = $GLOBALS['student_pay_api']['base_url'] . '/institutions/'
			. $this->getInstitutionIdForApi();
		$this->debug("StudentPaymentApi->getInstitutionData : GET " . $requestUrl);
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
	 * Update student payment settings for a group in the student payment API.
	 *
	 * @param $accessType string Currently one of: "not_required",
	 *        "activation_code", "direct_pay"
	 * @return StudentPayApiResult An instance of StudentPayApiResult.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function updateGroupPaymentSettings($accessType)
	{
		if (empty($accessType)) {
			throw new StudentPaymentException("No access type was specified.");
		}

		$this->curl->reset();

		$requestUrl = $GLOBALS['student_pay_api']['base_url'] . '/student_pay_settings';
		$this->debug("StudentPaymentApi->updateGroupPaymentSettings : PUT " . $requestUrl);
		$this->curl->setUrl($requestUrl);

		$requestData = json_encode(array(
			'institution_id' => (string)$this->getInstitutionIdForApi(),
			'access_type' => "$accessType"
		));
		$this->debug("Sending content: " . $requestData);

		$headers = array(
			'Authorization: Bearer ' . $GLOBALS['student_pay_api']['jwt_secret'],
			'Accept: application/json',
			'Content-Type: application/json',
		);
		$this->curl->setOption(CURLOPT_HTTPHEADER, $headers);
		$this->curl->setOption(CURLOPT_CUSTOMREQUEST, "PUT");
		$this->curl->setOption(CURLOPT_TIMEOUT, $GLOBALS['student_pay_api']['timeout']);
		$this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
		$this->curl->setOption(CURLOPT_POSTFIELDS, $requestData);
		$result = $this->curl->execute();
		$status = $this->curl->getInfo(CURLINFO_HTTP_CODE);

		$studentPayApiResult = $this->parseApiResponse($status, $result, [200, 201]);
		$this->curl->close();

		return $studentPayApiResult;
	}

	/**
	 * Delete student payment settings for a group in the student payment API.
	 *
	 * @return StudentPayApiResult An instance of StudentPayApiResult.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function deleteGroupPaymentSettings()
	{
		$this->curl->reset();

		$requestUrl = $GLOBALS['student_pay_api']['base_url'] . '/student_pay_settings';
		$this->debug("StudentPaymentApi->deleteGroupPaymentSettings : DELETE " . $requestUrl);
		$this->curl->setUrl($requestUrl);

		$requestData = json_encode(array(
			'institution_id' => (string)$this->getInstitutionIdForApi()
		));
		$this->debug("Sending content: " . $requestData);


		$headers = array(
			'Authorization: Bearer ' . $GLOBALS['student_pay_api']['jwt_secret'],
			'Accept: application/json',
		);
		$this->curl->setOption(CURLOPT_CUSTOMREQUEST, "DELETE");
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
	 * Get the activation type for a group.
	 *
	 * @return StudentPayApiResult A StudentPayApiResult object.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function getGroupAccessType()
	{
		$this->curl->reset();

		$requestUrl = $GLOBALS['student_pay_api']['base_url'] . '/student_pay_settings?' .
			\Sanitize::generateQueryStringFromMap(array(
				'institution_id' => (string)$this->getInstitutionIdForApi(),
			));
		$this->debug("StudentPaymentApi->getGroupAccessType : GET " . $requestUrl);
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

		$studentPayApiResult = $this->parseApiResponse($status, $result, [200, 404]);
		$this->curl->close();

		return $studentPayApiResult;
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

		if (0 == $status) {
			// curl returns 0 on http failure
			throw new StudentPaymentException("Unable to connect to student payment API.");
		}
		if (null == $apiResponse || '' == $apiResponse) {
			// json_decode failed to find valid json content
			if (!in_array($status, [204, 404])) {
				throw new StudentPaymentException("Unexpected content returned from student payment API: "
					. $responseBody);
			}
		}
		if (!in_array($status, $acceptableHttpStatusList)) {
			throw new StudentPaymentException(sprintf(
				"Unexpected HTTP status %d returned from student payment API. Content: %s", $status,
				$responseBody));
		}

		$studentPayApiResult = new StudentPayApiResult();
		$studentPayApiResult->setStudentPaymentStatus($apiResponse['status']);
		if (isset($apiResponse['message'])) {
			$studentPayApiResult->setApiUserMessage($apiResponse['message']);
		}
		if (isset($apiResponse['trial_expired_in'])) {
			$studentPayApiResult->setTrialExpiresInSeconds($apiResponse['trial_expired_in']);
		}
		if (isset($apiResponse['access_type'])) {
			$studentPayApiResult->setAccessType($apiResponse['access_type']);
		}
		if (isset($apiResponse['section_requires_student_payment'])) {
			$studentPayApiResult->setCourseRequiresStudentPayment($apiResponse['section_requires_student_payment']);
		}
		if (isset($apiResponse['payment_info'])) {
			$studentPayApiResult->setPaymentInfo($apiResponse['payment_info']);
		}
		if (isset($apiResponse['amount_cents'])) {
			$studentPayApiResult->setPaymentAmountInCents($apiResponse['amount_cents']);
		}
		if (isset($apiResponse['errors'])) {
			$studentPayApiResult->setErrors($apiResponse['errors']);
		}
		if (isset($apiResponse['branding'])) {
			$studentPayApiResult->setSchoolLogoUrl($apiResponse['branding']['logo_url']);
		}
		if (isset($apiResponse['branding'])) {
			$studentPayApiResult->setSchoolReceiptText($apiResponse['branding']['receipt_text']);
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

		if (isset($apiResponse['branding']['logo_url'])) {
			$lumenistrationInstitution->setSchoolLogoUrl($apiResponse['branding']['logo_url']);
		}

		if (isset($apiResponse['branding']['receipt_text'])) {
			$lumenistrationInstitution->setSchoolReceiptText($apiResponse['branding']['receipt_text']);
		}

		$allExternalIds = array();
		foreach ($apiResponse['external_ids'] as $key => $value) {
			$allExternalIds["$key"] = "$value"; // enclosed in quotes to force string type
		}
		$lumenistrationInstitution->setExternalIds($allExternalIds);

		return $lumenistrationInstitution;
	}

	/**
	 * Replace the instance group ID with a Lumen GUID, if available.
	 *
	 * @returns string An institution ID for feeding to the student payment API.
	 * @throws StudentPaymentException Thrown if a group ID was not provided
	 * when instantiating this class.
	 */
	private function getInstitutionIdForApi()
	{
		if (!is_null($this->institutionIdForApi)) {
			return $this->institutionIdForApi;
		}

		$lumenGuid = $this->studentPaymentDb->getLumenGuid();
		$this->institutionIdForApi = is_null($lumenGuid) || empty($lumenGuid)
			? $this->groupId : $lumenGuid;

		return $this->institutionIdForApi;
	}
}
