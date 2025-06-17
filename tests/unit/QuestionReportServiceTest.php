<?php

use OHM\Services\QuestionReportService;
use PHPUnit\Framework\TestCase;

/**
 * @covers OHM\Services\QuestionReportService
 */
final class QuestionReportServiceTest extends TestCase
{
    private $dbhMock;

    protected function setUp(): void
    {
        // Create a mock for the PDO database handler
        $this->dbhMock = $this->createMock(PDO::class);
    }

    /*
     * __construct
     */
    public function testConstructor()
    {
        $service = new QuestionReportService(
            $this->dbhMock,
            '2023-01-01',
            '2023-12-31',
            '2023-02-01',
            '2023-11-30'
        );

        $this->assertInstanceOf(QuestionReportService::class, $service);
    }

    /*
     * queryQuestions - Testing each parameter individually
     */

    // Test with startDate parameter
    public function testQueryQuestionsWithStartDate()
    {
        $startDate = '2023-01-01';

        // Create mock statement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($startDate) {
                // Verify that the start_date parameter is set correctly
                return isset($params[':start_date']) &&
                    $params[':start_date'] == strtotime($startDate);
            }));
        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['id' => 1, 'userights' => 2, 'ownerid' => 100, 'adddate' => time(), 'lastmoddate' => time(), 'qtype' => 'numeric', 'groupid' => 5]]);

        // Configure dbhMock to return our statement mock
        $this->dbhMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('AND qs.adddate >= :start_date'))
            ->willReturn($stmtMock);

        // Create service with only startDate
        $service = new QuestionReportService(
            $this->dbhMock,
            $startDate, // Only startDate is set
            '',
            '',
            ''
        );

        $result = $service->queryQuestions();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    // Test with endDate parameter
    public function testQueryQuestionsWithEndDate()
    {
        $endDate = '2023-12-31';

        // Create mock statement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($endDate) {
                // Verify that the end_date parameter is set correctly
                return isset($params[':end_date']) &&
                    $params[':end_date'] == strtotime($endDate . ' 23:59:59');
            }));
        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['id' => 1, 'userights' => 2, 'ownerid' => 100, 'adddate' => time(), 'lastmoddate' => time(), 'qtype' => 'multipleChoice', 'groupid' => 5]]);

        // Configure dbhMock to return our statement mock
        $this->dbhMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('AND qs.adddate <= :end_date'))
            ->willReturn($stmtMock);

        // Create service with only endDate
        $service = new QuestionReportService(
            $this->dbhMock,
            '',
            $endDate, // Only endDate is set
            '',
            ''
        );

        $result = $service->queryQuestions();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    // Test with startModDate parameter
    public function testQueryQuestionsWithStartModDate()
    {
        $startModDate = '2023-02-01';

        // Create mock statement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($startModDate) {
                // Verify that the start_mod_date parameter is set correctly
                return isset($params[':start_mod_date']) &&
                    $params[':start_mod_date'] == strtotime($startModDate);
            }));
        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['id' => 1, 'userights' => 2, 'ownerid' => 100, 'adddate' => time(), 'lastmoddate' => time(), 'qtype' => 'essay', 'groupid' => 5]]);

        // Configure dbhMock to return our statement mock
        $this->dbhMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('AND qs.lastmoddate >= :start_mod_date'))
            ->willReturn($stmtMock);

        // Create service with only startModDate
        $service = new QuestionReportService(
            $this->dbhMock,
            '',
            '',
            $startModDate, // Only startModDate is set
            ''
        );

        $result = $service->queryQuestions();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    // Test with endModDate parameter
    public function testQueryQuestionsWithEndModDate()
    {
        $endModDate = '2023-11-30';

        // Create mock statement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($endModDate) {
                // Verify that the end_mod_date parameter is set correctly
                return isset($params[':end_mod_date']) &&
                    $params[':end_mod_date'] == strtotime($endModDate . ' 23:59:59');
            }));
        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['id' => 1, 'userights' => 2, 'ownerid' => 100, 'adddate' => time(), 'lastmoddate' => time(), 'qtype' => 'matching', 'groupid' => 5]]);

        // Configure dbhMock to return our statement mock
        $this->dbhMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('AND qs.lastmoddate <= :end_mod_date'))
            ->willReturn($stmtMock);

        // Create service with only endModDate
        $service = new QuestionReportService(
            $this->dbhMock,
            '',
            '',
            '',
            $endModDate // Only endModDate is set
        );

        $result = $service->queryQuestions();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }


    // Test with minId parameter
    public function testQueryWithMinId()
    {
        $minId = 100;

        // Create mock statement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($minId) {
                // Verify that the min_id parameter is set correctly
                return isset($params[':min_id']) &&
                    $params[':min_id'] == $minId;
            }));
        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['id' => 100, 'userights' => 2, 'ownerid' => 100, 'adddate' => time(), 'lastmoddate' => time(), 'qtype' => 'numeric', 'groupid' => 5]]);

        // Configure dbhMock to return our statement mock
        $this->dbhMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('AND qs.id >= :min_id'))
            ->willReturn($stmtMock);

        // Create service with only minId
        $service = new QuestionReportService(
            $this->dbhMock,
            '', // startDate
            '', // endDate
            '', // startModDate
            '', // endModDate
            $minId // Only minId is set
        );

        $result = $service->queryQuestions();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    // Test with maxId parameter
    public function testQueryWithMaxId()
    {
        $maxId = 200;

        // Create mock statement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($maxId) {
                // Verify that the max_id parameter is set correctly
                return isset($params[':max_id']) &&
                    $params[':max_id'] == $maxId;
            }));
        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['id' => 150, 'userights' => 2, 'ownerid' => 100, 'adddate' => time(), 'lastmoddate' => time(), 'qtype' => 'numeric', 'groupid' => 5]]);

        // Configure dbhMock to return our statement mock
        $this->dbhMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('AND qs.id <= :max_id'))
            ->willReturn($stmtMock);

        // Create service with only maxId
        $service = new QuestionReportService(
            $this->dbhMock,
            '', // startDate
            '', // endDate
            '', // startModDate
            '', // endModDate
            null, // minId
            $maxId // Only maxId is set
        );

        $result = $service->queryQuestions();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    // Test with minAssessmentUsage parameter
    public function testQueryWithMinAssessmentUsage()
    {
        $minAssessmentUsage = 5;

        // Create mock statement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($minAssessmentUsage) {
                // Verify that the min_assessment_usage parameter is set correctly
                return isset($params[':min_assessment_usage']) &&
                    $params[':min_assessment_usage'] == $minAssessmentUsage;
            }));
        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['id' => 1, 'userights' => 2, 'ownerid' => 100, 'adddate' => time(), 'lastmoddate' => time(), 'qtype' => 'numeric', 'groupid' => 5, 'assessment_usage_count' => 5]]);

        // Configure dbhMock to return our statement mock
        $this->dbhMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('AND (SELECT COUNT(*) FROM imas_questions WHERE questionsetid = qs.id) >= :min_assessment_usage'))
            ->willReturn($stmtMock);

        // Create service with only minAssessmentUsage
        $service = new QuestionReportService(
            $this->dbhMock,
            '', // startDate
            '', // endDate
            '', // startModDate
            '', // endModDate
            null, // minId
            null, // maxId
            $minAssessmentUsage // Only minAssessmentUsage is set
        );

        $result = $service->queryQuestions();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    // Test with maxAssessmentUsage parameter
    public function testQueryWithMaxAssessmentUsage()
    {
        $maxAssessmentUsage = 10;

        // Create mock statement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($maxAssessmentUsage) {
                // Verify that the max_assessment_usage parameter is set correctly
                return isset($params[':max_assessment_usage']) &&
                    $params[':max_assessment_usage'] == $maxAssessmentUsage;
            }));
        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['id' => 1, 'userights' => 2, 'ownerid' => 100, 'adddate' => time(), 'lastmoddate' => time(), 'qtype' => 'numeric', 'groupid' => 5, 'assessment_usage_count' => 8]]);

        // Configure dbhMock to return our statement mock
        $this->dbhMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('AND (SELECT COUNT(*) FROM imas_questions WHERE questionsetid = qs.id) <= :max_assessment_usage'))
            ->willReturn($stmtMock);

        // Create service with only maxAssessmentUsage
        $service = new QuestionReportService(
            $this->dbhMock,
            '', // startDate
            '', // endDate
            '', // startModDate
            '', // endModDate
            null, // minId
            null, // maxId
            null, // minAssessmentUsage
            $maxAssessmentUsage // Only maxAssessmentUsage is set
        );

        $result = $service->queryQuestions();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    /*
     * generateReport
     */
    public function testGenerateReport()
    {
        // Create a service with mocked methods
        $service = $this->getMockBuilder(QuestionReportService::class)
            ->setConstructorArgs([$this->dbhMock, '', '', '', '', false])
            ->onlyMethods(['queryQuestions', 'aggregateQuestionData', 'queryUsers', 'queryGroups'])
            ->getMock();

        // Set up expectations for mocked methods
        $service->expects($this->once())
            ->method('queryQuestions')
            ->willReturn([['id' => 1, 'userights' => 2, 'ownerid' => 100, 'adddate' => time(), 'lastmoddate' => time(), 'qtype' => 'numeric', 'groupid' => 5]]);

        $service->expects($this->once())
            ->method('aggregateQuestionData');

        $service->expects($this->once())
            ->method('queryUsers')
            ->willReturn([['id' => 100, 'FirstName' => 'John', 'LastName' => 'Doe', 'rights' => 40, 'groupid' => 5, 'groupname' => 'Test Group']]);

        $service->expects($this->once())
            ->method('queryGroups')
            ->willReturn([['id' => 5, 'name' => 'Test Group', 'grouptype' => 'Test']]);

        // Call the method
        $result = $service->generateReport();

        // Assert the result
        $this->assertIsArray($result);
        $this->assertArrayHasKey('questions', $result);
        $this->assertArrayHasKey('users', $result);
        $this->assertArrayHasKey('groups', $result);
        $this->assertArrayHasKey('userRightsDistribution', $result);
        $this->assertArrayHasKey('questionTypeDistribution', $result);
    }

    /*
     * aggregateQuestionData
     */
    public function testAggregateQuestionData()
    {
        // Create a service with questions data
        $service = new QuestionReportService($this->dbhMock, '', '', '', '');

        // Use reflection to set the questions property
        $reflection = new ReflectionClass($service);
        $questionsProperty = $reflection->getProperty('questions');
        $questionsProperty->setAccessible(true);
        $questionsProperty->setValue($service, [
            ['id' => 1, 'userights' => '0', 'ownerid' => 100, 'adddate' => time(), 'lastmoddate' => time(), 'qtype' => 'numeric', 'groupid' => 5],
            ['id' => 2, 'userights' => '2', 'ownerid' => 101, 'adddate' => time(), 'lastmoddate' => time(), 'qtype' => 'multipleChoice', 'groupid' => 5],
            ['id' => 3, 'userights' => '4', 'ownerid' => 100, 'adddate' => time(), 'lastmoddate' => time(), 'qtype' => 'numeric', 'groupid' => 6]
        ]);

        // Call the method
        $service->aggregateQuestionData();

        // Get the userRightsDistribution property
        $userRightsDistributionProperty = $reflection->getProperty('userRightsDistribution');
        $userRightsDistributionProperty->setAccessible(true);
        $userRightsDistribution = $userRightsDistributionProperty->getValue($service);

        // Get the questionTypeDistribution property
        $questionTypeDistributionProperty = $reflection->getProperty('questionTypeDistribution');
        $questionTypeDistributionProperty->setAccessible(true);
        $questionTypeDistribution = $questionTypeDistributionProperty->getValue($service);

        // Get the uniqueUserIds property
        $uniqueUserIdsProperty = $reflection->getProperty('uniqueUserIds');
        $uniqueUserIdsProperty->setAccessible(true);
        $uniqueUserIds = $uniqueUserIdsProperty->getValue($service);

        // Get the uniqueGroupIds property
        $uniqueGroupIdsProperty = $reflection->getProperty('uniqueGroupIds');
        $uniqueGroupIdsProperty->setAccessible(true);
        $uniqueGroupIds = $uniqueGroupIdsProperty->getValue($service);

        // Assert the results
        $this->assertEquals(1, $userRightsDistribution['0']);
        $this->assertEquals(1, $userRightsDistribution['2']);
        $this->assertEquals(1, $userRightsDistribution['4']);
        $this->assertEquals(2, $questionTypeDistribution['numeric']);
        $this->assertEquals(1, $questionTypeDistribution['multipleChoice']);
        $this->assertCount(2, $uniqueUserIds);
        $this->assertContains(100, $uniqueUserIds);
        $this->assertContains(101, $uniqueUserIds);
        $this->assertCount(2, $uniqueGroupIds);
        $this->assertContains(5, $uniqueGroupIds);
        $this->assertContains(6, $uniqueGroupIds);
    }

    /*
     * queryUsers
     */
    public function testQueryUsers()
    {
        // Create a mock statement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->with([100, 101]);
        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                ['id' => 100, 'FirstName' => 'John', 'LastName' => 'Doe', 'rights' => 40, 'groupid' => 5, 'groupname' => 'Test Group'],
                ['id' => 101, 'FirstName' => 'Jane', 'LastName' => 'Smith', 'rights' => 40, 'groupid' => 6, 'groupname' => 'Another Group']
            ]);

        // Configure dbhMock to return our statement mock
        $this->dbhMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        // Create a service with uniqueUserIds
        $service = new QuestionReportService($this->dbhMock, '', '', '', '');

        // Use reflection to set the uniqueUserIds property
        $reflection = new ReflectionClass($service);
        $uniqueUserIdsProperty = $reflection->getProperty('uniqueUserIds');
        $uniqueUserIdsProperty->setAccessible(true);
        $uniqueUserIdsProperty->setValue($service, [100, 101]);

        // Call the method
        $result = $service->queryUsers();

        // Assert the result
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(100, $result[0]['id']);
        $this->assertEquals(101, $result[1]['id']);
    }

    /*
     * queryGroups
     */
    public function testQueryGroups()
    {
        // Create a mock statement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->with([5, 6]);
        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                ['id' => 5, 'name' => 'Test Group', 'grouptype' => 'Test'],
                ['id' => 6, 'name' => 'Another Group', 'grouptype' => 'Test']
            ]);

        // Configure dbhMock to return our statement mock
        $this->dbhMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        // Create a service with uniqueGroupIds
        $service = new QuestionReportService($this->dbhMock, '', '', '', '', false);

        // Use reflection to set the uniqueGroupIds property
        $reflection = new ReflectionClass($service);
        $uniqueGroupIdsProperty = $reflection->getProperty('uniqueGroupIds');
        $uniqueGroupIdsProperty->setAccessible(true);
        $uniqueGroupIdsProperty->setValue($service, [5, 6]);

        // Call the method
        $result = $service->queryGroups();

        // Assert the result
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(5, $result[0]['id']);
        $this->assertEquals(6, $result[1]['id']);
    }

    /*
     * getQuestions
     */
    public function testGetQuestions()
    {
        // Create a service
        $service = new QuestionReportService($this->dbhMock, '', '', '', '', false);

        // Use reflection to set the questions property
        $reflection = new ReflectionClass($service);
        $questionsProperty = $reflection->getProperty('questions');
        $questionsProperty->setAccessible(true);
        $questionsProperty->setValue($service, [
            ['id' => 1, 'userights' => '0', 'ownerid' => 100, 'adddate' => time(), 'lastmoddate' => time(), 'qtype' => 'numeric', 'groupid' => 5]
        ]);

        // Call the method
        $result = $service->getQuestions();

        // Assert the result
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['id']);
    }

    /*
     * getUserRightsDistribution
     */
    public function testGetUserRightsDistribution()
    {
        // Create a service
        $service = new QuestionReportService($this->dbhMock, '', '', '', '', false);

        // Use reflection to set the userRightsDistribution property
        $reflection = new ReflectionClass($service);
        $userRightsDistributionProperty = $reflection->getProperty('userRightsDistribution');
        $userRightsDistributionProperty->setAccessible(true);
        $userRightsDistributionProperty->setValue($service, [
            '0' => 1,
            '1' => 0,
            '2' => 2,
            '3' => 0,
            '4' => 3,
            'Unspecified' => 0
        ]);

        // Call the method
        $result = $service->getUserRightsDistribution();

        // Assert the result
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['0']);
        $this->assertEquals(2, $result['2']);
        $this->assertEquals(3, $result['4']);
    }

    /*
     * getUsers
     */
    public function testGetUsers()
    {
        // Create a service
        $service = new QuestionReportService($this->dbhMock, '', '', '', '', false);

        // Use reflection to set the users property
        $reflection = new ReflectionClass($service);
        $usersProperty = $reflection->getProperty('users');
        $usersProperty->setAccessible(true);
        $usersProperty->setValue($service, [
            ['id' => 100, 'FirstName' => 'John', 'LastName' => 'Doe', 'rights' => 40, 'groupid' => 5, 'groupname' => 'Test Group']
        ]);

        // Call the method
        $result = $service->getUsers();

        // Assert the result
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(100, $result[0]['id']);
    }

    /*
     * getQuestionTypeDistribution
     */
    public function testGetQuestionTypeDistribution()
    {
        // Create a service
        $service = new QuestionReportService($this->dbhMock, '', '', '', '', false);

        // Use reflection to set the questionTypeDistribution property
        $reflection = new ReflectionClass($service);
        $questionTypeDistributionProperty = $reflection->getProperty('questionTypeDistribution');
        $questionTypeDistributionProperty->setAccessible(true);
        $questionTypeDistributionProperty->setValue($service, [
            'numeric' => 5,
            'multipleChoice' => 3,
            'essay' => 2,
            'Unspecified' => 1
        ]);

        // Call the method
        $result = $service->getQuestionTypeDistribution();

        // Assert the result
        $this->assertIsArray($result);
        $this->assertEquals(5, $result['numeric']);
        $this->assertEquals(3, $result['multipleChoice']);
        $this->assertEquals(2, $result['essay']);
        $this->assertEquals(1, $result['Unspecified']);
    }

    /*
     * getGroups
     */
    public function testGetGroups()
    {
        // Create a service
        $service = new QuestionReportService($this->dbhMock, '', '', '', '', false);

        // Use reflection to set the groups property
        $reflection = new ReflectionClass($service);
        $groupsProperty = $reflection->getProperty('groups');
        $groupsProperty->setAccessible(true);
        $groupsProperty->setValue($service, [
            ['id' => 5, 'name' => 'Test Group', 'grouptype' => 'Test']
        ]);

        // Call the method
        $result = $service->getGroups();

        // Assert the result
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(5, $result[0]['id']);
    }

    /*
     * questionsToCSVArrays
     */
    public function testQuestionsToCSVArrays()
    {
        // Create a service
        $service = new QuestionReportService($this->dbhMock, '', '', '', '', false);

        // Use reflection to set the questions property
        $reflection = new ReflectionClass($service);
        $questionsProperty = $reflection->getProperty('questions');
        $questionsProperty->setAccessible(true);

        $currentTime = time();
        $questionsProperty->setValue($service, [
            ['id' => 1, 'userights' => '0', 'ownerid' => 100, 'adddate' => $currentTime, 'lastmoddate' => $currentTime, 'qtype' => 'numeric', 'groupid' => 5, 'assessment_usage_count' => 3]
        ]);

        // Call the method
        $result = $service->questionsToCSVArrays();

        // Assert the result
        $this->assertIsArray($result);
        $this->assertCount(2, $result); // Header row + 1 data row
        $this->assertEquals(['Question ID', 'User Rights', 'Question Type', 'Owner ID', 'Creation Date', 'Last Modified Date', 'Group ID', 'Assessment Usage Count'], $result[0]);
        $this->assertEquals(1, $result[1][0]); // Question ID
        $this->assertEquals('0', $result[1][1]); // User Rights
        $this->assertEquals('numeric', $result[1][2]); // Question Type
        $this->assertEquals(100, $result[1][3]); // Owner ID
        $this->assertEquals(date('Y-m-d H:i:s', $currentTime), $result[1][4]); // Creation Date
        $this->assertEquals(date('Y-m-d H:i:s', $currentTime), $result[1][5]); // Last Modified Date
        $this->assertEquals(5, $result[1][6]); // Group ID
        $this->assertEquals(3, $result[1][7]); // Assessment Usage Count
    }

    /*
     * usersToCSVArrays
     */
    public function testUsersToCSVArrays()
    {
        // Create a service
        $service = new QuestionReportService($this->dbhMock, '', '', '', '', false);

        // Use reflection to set the users property
        $reflection = new ReflectionClass($service);
        $usersProperty = $reflection->getProperty('users');
        $usersProperty->setAccessible(true);
        $usersProperty->setValue($service, [
            ['id' => 100, 'FirstName' => 'John', 'LastName' => 'Doe', 'rights' => 40, 'groupid' => 5, 'groupname' => 'Test Group']
        ]);

        // Call the method
        $result = $service->usersToCSVArrays();

        // Assert the result
        $this->assertIsArray($result);
        $this->assertCount(2, $result); // Header row + 1 data row
        $this->assertEquals(['ID', 'Name', 'Rights', 'Group Name'], $result[0]);
        $this->assertEquals(100, $result[1][0]); // ID
        $this->assertEquals('John Doe', $result[1][1]); // Name
        $this->assertEquals(40, $result[1][2]); // Rights
        $this->assertEquals('Test Group', $result[1][3]); // Group Name
    }

    /*
     * groupsToCSVArrays
     */
    public function testGroupsToCSVArrays()
    {
        // Create a service
        $service = new QuestionReportService($this->dbhMock, '', '', '', '', false);

        // Use reflection to set the groups property
        $reflection = new ReflectionClass($service);
        $groupsProperty = $reflection->getProperty('groups');
        $groupsProperty->setAccessible(true);
        $groupsProperty->setValue($service, [
            ['id' => 5, 'name' => 'Test Group', 'grouptype' => 'Test']
        ]);

        // Call the method
        $result = $service->groupsToCSVArrays();

        // Assert the result
        $this->assertIsArray($result);
        $this->assertCount(2, $result); // Header row + 1 data row
        $this->assertEquals(['ID', 'Name', 'Group Type'], $result[0]);
        $this->assertEquals(5, $result[1][0]); // ID
        $this->assertEquals('Test Group', $result[1][1]); // Name
        $this->assertEquals('Test', $result[1][2]); // Group Type
    }
}
