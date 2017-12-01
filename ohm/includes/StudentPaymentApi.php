<?php

namespace OHM;

require_once(__DIR__ . "/../../includes/sanitize.php");
require_once(__DIR__ . "/../exceptions/StudentPaymentException.php");
require_once(__DIR__ . "/../models/StudentPayApiResult.php");
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

		$headers = array('Authorization: Bearer ' . $GLOBALS['student_pay_api']['jwt_secret']);
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

		$requestData = array(
			'institution_id' => "$this->groupId",
			'section_id' => "$this->courseId",
			'enrollment_id' => "$enrollmentId",
			'code' => $activationCode
		);

		$headers = array(
			'Authorization: Bearer ' . $GLOBALS['student_pay_api']['jwt_secret'],
			'Content-Type: application/json',
		);
		$this->curl->setOption(CURLOPT_HTTPHEADER, $headers);
		$this->curl->setOption(CURLOPT_TIMEOUT, $GLOBALS['student_pay_api']['timeout']);
		$this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
		$this->curl->setOption(CURLOPT_POSTFIELDS, json_encode($requestData));
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

		$requestData = array(
			'institution_id' => "$this->groupId",
			'section_id' => "$this->courseId",
			'enrollment_id' => "$enrollmentId"
		);

		$headers = array(
			'Authorization: Bearer ' . $GLOBALS['student_pay_api']['jwt_secret'],
			'Content-Type: application/json',
		);
		$this->curl->setOption(CURLOPT_HTTPHEADER, $headers);
		$this->curl->setOption(CURLOPT_TIMEOUT, $GLOBALS['student_pay_api']['timeout']);
		$this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
		$this->curl->setOption(CURLOPT_POSTFIELDS, json_encode($requestData));
		$result = $this->curl->execute();
		$status = $this->curl->getInfo(CURLINFO_HTTP_CODE);

		$studentPayApiResult = $this->parseApiResponse($status, $result, [200, 204]);
		$this->curl->close();

		return $studentPayApiResult;
	}

	/**
	 * Notify the student payment API that a student has started an assessment while under trial.
	 * This is for metrics.
	 *
	 * @return StudentPayApiResult An instance of StudentPayApiResult.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function logBeginAssessmentDuringTrial()
	{
		$this->curl->reset();

		$enrollmentId = $this->studentPaymentDb->getStudentEnrollmentId();

		$requestUrl = $GLOBALS['student_pay_api']['base_url'] . '/enrollment_events';
		$this->debug("Student API URL = " . $requestUrl);
		$this->curl->setUrl($requestUrl);

		$requestData = array(
			'event_type' => 'free_quiz_started',
			'institution_id' => "$this->groupId",
			'section_id' => "$this->courseId",
			'enrollment_id' => "$enrollmentId"
		);

		$headers = array(
			'Authorization: Bearer ' . $GLOBALS['student_pay_api']['jwt_secret'],
			'Content-Type: application/json',
		);
		$this->curl->setOption(CURLOPT_HTTPHEADER, $headers);
		$this->curl->setOption(CURLOPT_TIMEOUT, $GLOBALS['student_pay_api']['timeout']);
		$this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
		$this->curl->setOption(CURLOPT_POSTFIELDS, json_encode($requestData));
		$result = $this->curl->execute();
		$status = $this->curl->getInfo(CURLINFO_HTTP_CODE);

		$studentPayApiResult = $this->parseApiResponse($status, $result, [200, 204]);
		$this->curl->close();

		return $studentPayApiResult;
	}

	/**
	 * Notify the student payment API that a student has declined a trial. This is for metrics.
	 *
	 * @return StudentPayApiResult An instance of StudentPayApiResult.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function logDeclineTrial()
	{
		$this->curl->reset();

		$enrollmentId = $this->studentPaymentDb->getStudentEnrollmentId();

		$requestUrl = $GLOBALS['student_pay_api']['base_url'] . '/enrollment_events';
		$this->debug("Student API URL = " . $requestUrl);
		$this->curl->setUrl($requestUrl);

		$requestData = array(
			'event_type' => 'refused_trial_start',
			'institution_id' => "$this->groupId",
			'section_id' => "$this->courseId",
			'enrollment_id' => "$enrollmentId"
		);

		$headers = array(
			'Authorization: Bearer ' . $GLOBALS['student_pay_api']['jwt_secret'],
			'Content-Type: application/json',
		);
		$this->curl->setOption(CURLOPT_HTTPHEADER, $headers);
		$this->curl->setOption(CURLOPT_TIMEOUT, $GLOBALS['student_pay_api']['timeout']);
		$this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
		$this->curl->setOption(CURLOPT_POSTFIELDS, json_encode($requestData));
		$result = $this->curl->execute();
		$status = $this->curl->getInfo(CURLINFO_HTTP_CODE);

		$studentPayApiResult = $this->parseApiResponse($status, $result, [200, 204]);
		$this->curl->close();

		return $studentPayApiResult;
	}

	/**
	 * Parse a student payment API response.
	 *
	 * @param $status integer An HTTP status code.
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
		if (null == $apiResponse) {
			// json_decode failed to find valid json content
			throw new StudentPaymentException("Unexpected content returned from student payment API: "
				. $responseBody);
		}
		if (!isset($apiResponse['status'])) {
			// All endpoints should return a status in the json payload.
			throw new StudentPaymentException(sprintf(
				"Student payment API did not return a status in JSON payload. Content: %s", $status,
				$responseBody));
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
		if (isset($apiResponse['section_requires_student_payment'])) {
			$studentPayApiResult->setCourseRequiresStudentPayment($apiResponse['section_requires_student_payment']);
		}
		if (isset($apiResponse['errors'])) {
			$studentPayApiResult->setErrors($apiResponse['errors']);
		}

		return $studentPayApiResult;
	}

	/**
	 * Determine if an access code is well formed.
	 *
	 * @param $code string A valid assessment access code.
	 * @return string Null on validation success. Error message on validation failure.
	 */
	public static function validateAccessCodeStructure($code)
	{
		$accessCodeMinLength = $GLOBALS['student_pay_api']['access_code_min_length'];
		$accessCodeMaxLength = $GLOBALS['student_pay_api']['access_code_max_length'];

		$sanitizedCode = preg_replace("/[^234679acdefghjkmnpqrtwxyz]/i", "", $code);
		if ($sanitizedCode != $code) {
			return "Invalid characters found in access code. Please check your access code and try again.";
		}

		if ($accessCodeMinLength > strlen($sanitizedCode) || $accessCodeMaxLength < strlen($sanitizedCode)) {
			return sprintf("Access code must be between %d and %d characters.", $accessCodeMinLength,
				$accessCodeMaxLength);
		}

		return null;
	}

}
