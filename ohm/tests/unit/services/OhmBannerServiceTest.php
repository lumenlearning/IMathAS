<?php

namespace OHM\Tests;

use DateTime;
use OHM\Models\Banner;
use OHM\Services\OhmBannerService;
use PDO;
use PHPUnit\Framework\TestCase;


/**
 * @covers OhmBannerService
 */
final class OhmBannerServiceTest extends TestCase
{
    const EMPTY_VIEW_FILE = '/ohm/tests/fixtures/empty.php';

    private $ohmBannerService;

    /* @var PDO */
    private $dbh;
    /* @var Banner */
    private $banner;

    function setUp(): void
    {
        $this->dbh = $this->createMock(\PDO::class);
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
                'created_at' => '2020-09-28 12:00:00',
            ])
            ->willReturnOnConsecutiveCalls(false);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        $realBanner = new Banner($this->dbh);
        $realBanner
            ->setId(42)
            ->setEnabled(true)
            ->setDismissible(true)
            ->setDisplayStudent(true)
            ->setDisplayTeacher(true)
            ->setDescription('Sample description')
            ->setStudentTitle('Student Title')
            ->setStudentContent('Student Content')
            ->setTeacherTitle('Teacher Title')
            ->setTeacherContent('Teacher Content')
            ->setStartAt(DateTime::createFromFormat('Y-m-d H:i:s', '2020-09-01 00:00:00'))
            ->setEndAt(DateTime::createFromFormat('Y-m-d H:i:s', '2020-09-28 12:00:00'))
            ->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2020-09-28 12:00:00'));
        $this->banner = $this->createMock(Banner::class);
        $this->banner->method('findEnabledAndAvailable')->willReturn([$realBanner]);

        $this->ohmBannerService = new OhmBannerService($this->dbh, 1, 0);
    }

    /*
     * showTeacherBannersForTeachersOnly
     */

    public function testShowTeacherBannerForTeachersOnly_UserIsTeacher(): void
    {
        foreach ([20, 40, 75, 100] as $userRights) {
            $this->ohmBannerService
                ->setUserRights($userRights)
                ->setBannerForTesting($this->banner)
                ->setDisplayOnlyOncePerBanner(false);

            $result = $this->ohmBannerService->showTeacherBannersForTeachersOnly();

            $this->assertTrue($result, 'banner should be displayed for teachers. (user rights > 15)');
        }
    }

    public function testShowTeacherBannerForTeachersOnly_UserIsNotTeacher(): void
    {
        foreach ([0, 5, 10, 12, 15] as $userRights) {
            $this->ohmBannerService
                ->setUserRights($userRights)
                ->setBannerForTesting($this->banner)
                ->setDisplayOnlyOncePerBanner(false);

            $result = $this->ohmBannerService->showTeacherBannersForTeachersOnly();

            $this->assertFalse($result, 'banner should not be displayed for students. (user rights <= 15)');
        }
    }

    public function testShowTeacherBannerForTeachersOnly_OnlyOnce(): void
    {
        $this->ohmBannerService
            ->setUserRights(20)
            ->setBannerForTesting($this->banner)
            ->setDisplayOnlyOncePerBanner(true);

        $result = $this->ohmBannerService->showTeacherBannersForTeachersOnly();
        $this->assertTrue($result, 'the banner should be displayed on the first method call.');

        $result = $this->ohmBannerService->showTeacherBannersForTeachersOnly();
        $this->assertFalse($result, 'the banner should be displayed only once.');
    }

    /*
     * showStudentBannersForStudentsOnly
     */

    public function testShowStudentBannerForStudentsOnly_UserIsStudent(): void
    {
        foreach ([0, 5, 10, 12, 15] as $userRights) {
            $this->ohmBannerService
                ->setUserRights($userRights)
                ->setBannerForTesting($this->banner)
                ->setDisplayOnlyOncePerBanner(false);

            $result = $this->ohmBannerService->showStudentBannersForStudentsOnly();

            $this->assertTrue($result, 'banner should be displayed for students. (user rights <= 15)');
        }
    }

    public function testShowStudentBannerForStudentsOnly_UserIsNotStudent(): void
    {
        foreach ([20, 40, 75, 100] as $userRights) {
            $this->ohmBannerService
                ->setUserRights($userRights)
                ->setBannerForTesting($this->banner)
                ->setDisplayOnlyOncePerBanner(false);

            $result = $this->ohmBannerService->showStudentBannersForStudentsOnly();

            $this->assertFalse($result, 'banner should not be displayed for teachers. (user rights > 15)');
        }
    }

    public function testShowStudentBannerForStudentsOnly_OnlyOnce(): void
    {
        $this->ohmBannerService
            ->setUserRights(10)
            ->setBannerForTesting($this->banner)
            ->setDisplayOnlyOncePerBanner(true);

        $result = $this->ohmBannerService->showStudentBannersForStudentsOnly();
        $this->assertTrue($result, 'the banner should be displayed on the first method call.');

        $result = $this->ohmBannerService->showStudentBannersForStudentsOnly();
        $this->assertFalse($result, 'the banner should be displayed only once.');
    }
}
