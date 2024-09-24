<?php


namespace OHM\Tests\Unit\includes;

use OHM\Includes\LtiQueueLogger;
use PHPUnit\Framework\TestCase;


/**
 * @covers LtiQueueLogger
 */
final class LtiQueueLoggerTest extends TestCase
{
    /*
     * getUserIdAndAssessmentIdFromHash
     */

    public function testGetUserIdAndAssessmentIdFromHash(): void
    {
        $hash = '8562138-3856143';

        $aidAndUid = LtiQueueLogger::getUserIdAndAssessmentIdFromHash($hash);

        $this->assertEquals(8562138, $aidAndUid['assessment_id']);
        $this->assertEquals(3856143, $aidAndUid['user_id']);
    }

    public function testGetUserIdAndAssessmentIdFromHash_NoData(): void
    {
        $aidAndUid = LtiQueueLogger::getUserIdAndAssessmentIdFromHash('meow');

        $this->assertNull($aidAndUid['assessment_id']);
        $this->assertNull($aidAndUid['user_id']);
    }

    public function testGetUserIdAndAssessmentIdFromHash_EmptyHash(): void
    {
        $aidAndUid = LtiQueueLogger::getUserIdAndAssessmentIdFromHash('');

        $this->assertNull($aidAndUid['assessment_id']);
        $this->assertNull($aidAndUid['user_id']);
    }

    public function testGetUserIdAndAssessmentIdFromHash_NullHash(): void
    {
        $aidAndUid = LtiQueueLogger::getUserIdAndAssessmentIdFromHash(null);

        $this->assertNull($aidAndUid['assessment_id']);
        $this->assertNull($aidAndUid['user_id']);
    }

    /*
     * generateLtiqueueRowDetails
     */

    public function testGenerateLtiqueueRowDetails(): void
    {
        $rowData = [
            'hash' => '8562138-3856143',
            'sourcedid' => '110363-1246813-12812888-3844898-103f57b65885349a84a258b5fffe16b4026f8a5f:|:https://lumen.instructure.com/api/lti/v1/tools/110363/grade_passback:|:pizzaMeow:|:u',
            'grade' => 1,
            'failures' => 0,
            'sendon' => 1567201539,
            'userid' => 42,
            'assessmentid' => 1234,
            'keyseturl' => 'https://localhost/meow',
            'isstu' => 1,
            'addedon' => 1726608036,
        ];

        $logDetails = LtiQueueLogger::generateLtiqueueRowDetails($rowData);

        $this->assertEquals($rowData['hash'], $logDetails['hash']);
        $this->assertEquals($rowData['sourcedid'], $logDetails['sourcedid']);
        $this->assertEquals($rowData['userid'], $logDetails['userid']);
        $this->assertEquals($rowData['assessmentid'], $logDetails['assessmentid']);
        $this->assertEquals($rowData['isstu'], $logDetails['isstu']);
        $this->assertEquals($rowData['grade'], $logDetails['grade']);
        $this->assertEquals($rowData['failures'], $logDetails['failures']);
        $this->assertEquals($rowData['addedon'], $logDetails['addedon']);
    }

    public function testGenerateLtiqueueRowDetails_RowMissingUserInfo(): void
    {
        $rowData = [
            'hash' => '8562138-3856143',
            'sourcedid' => '110363-1246813-12812888-3844898-103f57b65885349a84a258b5fffe16b4026f8a5f:|:https://lumen.instructure.com/api/lti/v1/tools/110363/grade_passback:|:pizzaMeow:|:u',
            'grade' => 1,
            'failures' => 0,
            'sendon' => 1567201539,
            'userid' => null,
            'assessmentid' => null,
            'keyseturl' => 'https://localhost/meow',
            'isstu' => 1,
            'addedon' => 1726608036,
        ];

        $logDetails = LtiQueueLogger::generateLtiqueueRowDetails($rowData);

        $this->assertEquals($rowData['hash'], $logDetails['hash']);
        $this->assertEquals($rowData['sourcedid'], $logDetails['sourcedid']);
        $this->assertEquals(3856143, $logDetails['userid']);
        $this->assertEquals(8562138, $logDetails['assessmentid']);
        $this->assertEquals($rowData['isstu'], $logDetails['isstu']);
        $this->assertEquals($rowData['grade'], $logDetails['grade']);
        $this->assertEquals($rowData['failures'], $logDetails['failures']);
        $this->assertEquals($rowData['addedon'], $logDetails['addedon']);
    }

    public function testGenerateLtiqueueRowDetails_AllMissingUserInfo(): void
    {
        $rowData = [
            'hash' => 'meow',
            'sourcedid' => '110363-1246813-12812888-3844898-103f57b65885349a84a258b5fffe16b4026f8a5f:|:https://lumen.instructure.com/api/lti/v1/tools/110363/grade_passback:|:pizzaMeow:|:u',
            'grade' => 1,
            'failures' => 0,
            'sendon' => 1567201539,
            'userid' => null,
            'assessmentid' => null,
            'keyseturl' => 'https://localhost/meow',
            'isstu' => 1,
            'addedon' => 1726608036,
        ];

        $logDetails = LtiQueueLogger::generateLtiqueueRowDetails($rowData);

        $this->assertEquals($rowData['hash'], $logDetails['hash']);
        $this->assertEquals($rowData['sourcedid'], $logDetails['sourcedid']);
        $this->assertNull($logDetails['userid']);
        $this->assertNull($logDetails['assessmentid']);
        $this->assertEquals($rowData['isstu'], $logDetails['isstu']);
        $this->assertEquals($rowData['grade'], $logDetails['grade']);
        $this->assertEquals($rowData['failures'], $logDetails['failures']);
        $this->assertEquals($rowData['addedon'], $logDetails['addedon']);
    }

    public function testGenerateLtiqueueRowDetails_NullHash(): void
    {
        $rowData = [
            'hash' => null,
            'sourcedid' => '110363-1246813-12812888-3844898-103f57b65885349a84a258b5fffe16b4026f8a5f:|:https://lumen.instructure.com/api/lti/v1/tools/110363/grade_passback:|:pizzaMeow:|:u',
            'grade' => 1,
            'failures' => 0,
            'sendon' => 1567201539,
            'userid' => 42,
            'assessmentid' => 1234,
            'keyseturl' => 'https://localhost/meow',
            'isstu' => 1,
            'addedon' => 1726608036,
        ];

        $logDetails = LtiQueueLogger::generateLtiqueueRowDetails($rowData);

        $this->assertNull($logDetails['hash']);
        $this->assertEquals($rowData['sourcedid'], $logDetails['sourcedid']);
        $this->assertEquals($rowData['userid'], $logDetails['userid']);
        $this->assertEquals($rowData['assessmentid'], $logDetails['assessmentid']);
        $this->assertEquals($rowData['isstu'], $logDetails['isstu']);
        $this->assertEquals($rowData['grade'], $logDetails['grade']);
        $this->assertEquals($rowData['failures'], $logDetails['failures']);
        $this->assertEquals($rowData['addedon'], $logDetails['addedon']);
    }
}