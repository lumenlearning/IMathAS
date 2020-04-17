<?php
require(__DIR__ . '/../../includes/TeacherAuditLog.php');

use PHPUnit\Framework\TestCase;

/**
 * @covers TeacherAuditLog
 */
final class TeacherAuditLogTest extends TestCase
{
    public static $course1 = array(
        array(
            'userid'=>1,
            'courseid'=>1,
            'action'=>"Question Settings Change",
            'itemid'=>1,
            'metadata'=>'{"source":"TeacherAuditLogTest.php"}',
        ),
    );
    public static $course3 = array(
        array(
            'userid'=>1,
            'courseid'=>3,
            'action'=>"Assessment Settings Change",
            'itemid'=>1,
            'metadata'=>'{"source":"TeacherAuditLogTest.php"}',
        ),
        array(
            'userid'=>1,
            'courseid'=>3,
            'action'=>"Clear Attempts",
            'itemid'=>1,
            'metadata'=>'{"source":"TeacherAuditLogTest.php"}',
        ),
    );

    public function setUp()
    {
        try {
            $GLOBALS['DBH'] = new PDO("sqlite::memory:");
            $GLOBALS['DBH']->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        } catch(PDOException $e) {
            die("<p>Could not connect to database: <b>" . $e->getMessage() . "</b></p></div></body></html>");
        }
        //set initialization data model
        $GLOBALS['userid'] = 1;
        $query = 'CREATE TABLE `imas_teacher_audit_log` (
          `id` int(10) PRIMARY KEY,
          `userid` int(10),
          `courseid` int(10),
          `action` VARCHAR(50),
          `itemid` int(10),
          `created_at` DATETIME DEFAULT (datetime(\'now\', \'localtime\')),
          `metadata` text)';
        $stm = $GLOBALS['DBH']->prepare($query);
        $stm->execute();
        //insert a test teacher action
        $query = "INSERT INTO imas_teacher_audit_log (userid,courseid,action,itemid,metadata) VALUES "
            . "(?, ?, ?, ?, ?)";
        $stm = $GLOBALS['DBH']->prepare($query);
        foreach (array_merge(self::$course1, self::$course3) as $row) {
            $stm->execute(array_values($row));
        }
    }

    /*
     * AssessmentSettingsChangeRecorded
     */
    public function testFindActionsByCourse()
    {
        $result = TeacherAuditLog::findActionsByCourse(3);
        unset($result[0]['id'], $result[0]['created_at']);
        $this->assertEquals(self::$course3[0], $result[0]);
    }

    /*
     * AssessmentSettingsChangeRecorded
     */
    public function testFindCourseItemAction()
    {
        $item = self::$course3[0];
        $result = TeacherAuditLog::findCourseItemAction($item['courseid'], $item['itemid'], $item['action']);
        unset($result[0]['id'], $result[0]['created_at']);
        $this->assertEquals(self::$course3[0], $result[0]);
    }

    /*
     * AssessmentSettingsChangeRecorded
     */
    public function testFindCourseAction()
    {
        $item = self::$course3[0];
        $result = TeacherAuditLog::findCourseAction($item['courseid'], $item['action']);
        unset($result[0]['id'], $result[0]['created_at']);
        $this->assertEquals(self::$course3[0], $result[0]);
    }

    /*
     * Invalid Action Not Recorded
     */
    public function testInvalidActionNotRecorded()
    {
        $action = 'Invalid Action';
        $item = self::$course1[0];
        $result = TeacherAuditLog::addTracking($item['courseid'], $action, $item['itemid']);
        $this->assertFalse($result);
    }

    /*
     * AssessmentSettingsChangeRecorded
     */
    public function testAssessmentSettingsChangeRecorded()
    {
        $action = 'Assessment Settings Change';
        $item = self::$course1[0];
        $result = TeacherAuditLog::addTracking($item['courseid'], $action, $item['itemid']);
        $this->assertTrue($result);
    }

    /*
     * MassAssessmentSettingsChangeRecorded
     */
    public function testMassAssessmentSettingsChangeRecorded()
    {
        $action = 'Mass Assessment Settings Change';
        $item = self::$course1[0];
        $result = TeacherAuditLog::addTracking($item['courseid'], $action);
        $this->assertTrue($result);
    }

    /*
     * MassDateChangeRecorded
     */
    public function testMassDateChangeRecorded()
    {
        $action = 'Mass Date Change';
        $item = self::$course1[0];
        $result = TeacherAuditLog::addTracking($item['courseid'], $action);
        $this->assertTrue($result);
    }

    /*
     * QuestionSettingsChangeRecorded
     */
    public function testQuestionSettingsChangeRecorded()
    {
        $action = 'Question Settings Change';
        $item = self::$course1[0];
        $result = TeacherAuditLog::addTracking($item['courseid'], $action, $item['itemid']);
        $this->assertTrue($result);
    }

    /*
     * ClearAttemptsRecorded
     */
    public function testClearAttemptsRecorded()
    {
        $action = 'Clear Attempts';
        $item = self::$course1[0];
        $result = TeacherAuditLog::addTracking($item['courseid'], $action, $item['itemid']);
        $this->assertTrue($result);
    }

    /*
     * ClearScoresRecorded
     */
    public function testClearScoresRecorded()
    {
        $action = 'Clear Scores';
        $item = self::$course1[0];
        $result = TeacherAuditLog::addTracking($item['courseid'], $action, $item['itemid']);
        $this->assertTrue($result);
    }

    /*
     * DeleteItemRecorded
     */
    public function testDeleteItemRecorded()
    {
        $action = 'Delete Item';
        $item = self::$course1[0];
        $result = TeacherAuditLog::addTracking($item['courseid'], $action, $item['itemid']);
        $this->assertTrue($result);
    }

    /*
     * UnenrollRecorded
     */
    public function testUnenrollRecorded()
    {
        $action = 'Unenroll';
        $item = self::$course1[0];
        $result = TeacherAuditLog::addTracking($item['courseid'], $action, $item['itemid']);
        $this->assertTrue($result);
    }

    /*
     * ChangeGradesRecorded
     */
    public function testChangeGradesRecorded()
    {
        $action = 'Change Grades';
        $item = self::$course1[0];
        $result = TeacherAuditLog::addTracking($item['courseid'], $action, $item['itemid']);
        $this->assertTrue($result);
    }

}
