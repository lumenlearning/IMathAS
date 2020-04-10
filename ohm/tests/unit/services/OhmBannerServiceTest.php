<?php

namespace OHM\Tests;

use OHM\Services\OhmBannerService;
use PHPUnit\Framework\TestCase;


/**
 * @covers OhmBannerService
 */
final class OhmBannerServiceTest extends TestCase
{
    const EMPTY_VIEW_FILE = '/ohm/tests/fixtures/empty.php';

    private $ohmBanner;

    function setUp(): void
    {
        $this->dbh = $this->createMock(\PDO::class);
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

        $this->ohmBanner = new OhmBannerService($this->dbh, 1, 0);
    }

    /*
     * showTeacherBannersForTeachersOnly
     */

    public function testShowTeacherBannerForTeachersOnly_UserIsTeacher(): void
    {
        foreach ([20, 40, 75, 100] as $userRights) {
            $this->ohmBanner->setUserRights($userRights);

            $result = $this->ohmBanner->showTeacherBannersForTeachersOnly();

            $this->assertTrue($result, 'banner should be displayed for teachers. (user rights > 15)');
        }
    }

    public function testShowTeacherBannerForTeachersOnly_UserIsNotTeacher(): void
    {
        foreach ([0, 5, 10, 12, 15] as $userRights) {
            $this->ohmBanner->setUserRights($userRights);

            $result = $this->ohmBanner->showTeacherBannersForTeachersOnly();

            $this->assertFalse($result, 'banner should not be displayed for students. (user rights <= 15)');
        }
    }

    public function testShowTeacherBannerForTeachersOnly_OnlyOnce(): void
    {
        $this->ohmBanner
            ->setUserRights(20)
            ->setDisplayOnlyOncePerBanner(true);

        $result = $this->ohmBanner->showTeacherBannersForTeachersOnly();
        $this->assertTrue($result, 'the banner should be displayed on the first method call.');

        $result = $this->ohmBanner->showTeacherBannersForTeachersOnly();
        $this->assertFalse($result, 'the banner should be displayed only once.');
    }

    /*
     * showStudentBannersForStudentsOnly
     */

    public function testShowStudentBannerForStudentsOnly_UserIsStudent(): void
    {
        foreach ([0, 5, 10, 12, 15] as $userRights) {
            $this->ohmBanner->setUserRights($userRights);

            $result = $this->ohmBanner->showStudentBannersForStudentsOnly();

            $this->assertTrue($result, 'banner should be displayed for students. (user rights <= 15)');
        }
    }

    public function testShowStudentBannerForStudentsOnly_UserIsNotStudent(): void
    {
        foreach ([20, 40, 75, 100] as $userRights) {
            $this->ohmBanner->setUserRights($userRights);

            $result = $this->ohmBanner->showStudentBannersForStudentsOnly();

            $this->assertFalse($result, 'banner should not be displayed for teachers. (user rights > 15)');
        }
    }

    public function testShowStudentBannerForStudentsOnly_OnlyOnce(): void
    {
        $this->ohmBanner
            ->setUserRights(10)
            ->setDisplayOnlyOncePerBanner(true);

        $result = $this->ohmBanner->showStudentBannersForStudentsOnly();
        $this->assertTrue($result, 'the banner should be displayed on the first method call.');

        $result = $this->ohmBanner->showStudentBannersForStudentsOnly();
        $this->assertFalse($result, 'the banner should be displayed only once.');
    }

    /*
     * showTeacherBanners
     */

    /*
     * showStudentBanners
     */
}
