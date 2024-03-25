<?php

namespace OHM\Tests;

use DateTime;
use Exception;
use OHM\Exceptions\DatabaseWriteException;
use OHM\Models\Banner;
use PDO;
use PHPUnit\Framework\TestCase;


/**
 * @covers Banner
 */
final class BannerTest extends TestCase
{
    private $banner;

    /* @var PDO */
    private $dbh;

    function setUp(): void
    {
        $this->dbh = $this->createMock(\PDO::class);

        $this->banner = new Banner($this->dbh);
    }

    /*
     * find
     */

    public function testFind(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('rowCount')->willReturn(1);
        $pdoStatement->method('fetch')->willReturn([
            'id' => 42,
            'is_enabled' => 1,
            'is_dismissible' => 1,
            'display_student' => 1,
            'display_teacher' => 1,
            'description' => 'Sample description',
            'student_title' => 'Student Title',
            'student_content' => 'Student Content',
            'teacher_title' => 'Teacher Title',
            'teacher_content' => 'Teacher Content',
            'start_at' => '2020-09-01 00:00:00',
            'end_at' => '2020-09-28 12:00:00',
            'created_at' => '2020-01-02 18:41:17',
        ]);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        $result = $this->banner->find(42);

        $this->assertTrue($result, 'should return true if BannerDismissal was found.');
        $this->assertEquals(42, $this->banner->getId(), 'should return the correct value from the DB');
        $this->assertTrue($this->banner->getEnabled(), 'should return the correct value from the DB');
        $this->assertTrue($this->banner->getDismissible(), 'should return the correct value from the DB');
        $this->assertTrue($this->banner->getDisplayStudent(), 'should return the correct value from the DB');
        $this->assertTrue($this->banner->getDisplayTeacher(), 'should return the correct value from the DB');
        $this->assertEquals($this->banner->getDescription(), 'Sample description', 'should return the correct value from the DB');
        $this->assertEquals($this->banner->getStudentTitle(), 'Student Title', 'should return the correct value from the DB');
        $this->assertEquals($this->banner->getStudentContent(), 'Student Content', 'should return the correct value from the DB');
        $this->assertEquals($this->banner->getTeacherTitle(), 'Teacher Title', 'should return the correct value from the DB');
        $this->assertEquals($this->banner->getTeacherContent(), 'Teacher Content', 'should return the correct value from the DB');
        $this->assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', '2020-09-01 00:00:00'), $this->banner->getStartAt(), 'should return the correct value from the DB');
        $this->assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', '2020-09-28 12:00:00'), $this->banner->getEndAt(), 'should return the correct value from the DB');
        $this->assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', '2020-01-02 18:41:17'), $this->banner->getCreatedAt(), 'should return the correct value from the DB');
    }

    public function testFind_NotFound(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('rowCount')->willReturn(0);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        $result = $this->banner->find(42);

        $this->assertFalse($result, 'should return false if ID was not found.');
    }

    /*
     * save
     */

    public function testSave(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('execute')->willReturn(true);
        $this->dbh->method('prepare')->willReturn($pdoStatement);
        $this->dbh->method('lastInsertId')->willReturn('43');

        // A light check for a DB write.
        $pdoStatement->expects($this->once())->method('execute');

        $result = $this->banner->save();

        $this->assertTrue($result, 'should return true on successful save.');
        $this->assertEquals(43, $this->banner->getId(), 'the last inserted row ID should be set.');
    }

    public function testSave_Failed(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('execute')->willReturn(false);
        $pdoStatement->method('errorInfo')->willReturn(['oops', 'it', 'failed']);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        // An exception should be thrown if we can't write to the DB.
        $this->expectException(DatabaseWriteException::class);

        $this->banner->save();
    }

    /*
     * findEnabledAndAvailable
     */

    public function testFindEnabledAndAvailable(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('rowCount')->willReturn(1);
        $pdoStatement->method('fetch')
            ->willReturn([
                'id' => 42,
                'is_enabled' => 1,
                'is_dismissible' => 1,
                'display_student' => 1,
                'display_teacher' => 1,
                'description' => 'Sample description',
                'student_title' => 'Student Title',
                'student_content' => 'Student Content',
                'teacher_title' => 'Teacher Title',
                'teacher_content' => 'Teacher Content',
                'start_at' => '2020-09-01 00:00:00',
                'end_at' => '2020-09-28 12:00:00',
                'created_at' => '2020-01-02 18:41:17',
            ]);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        $result = $this->banner->findEnabledAndAvailable();

        $this->assertCount(1, $result, 'array should contain one Banner object.');
        $this->assertEquals(42, $result[0]->getId(), 'Banner ID should match DB value.');
    }
}
