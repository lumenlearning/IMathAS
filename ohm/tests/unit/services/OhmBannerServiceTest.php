<?php

namespace OHM\tests\unit\services;

use DateTime;
use OHM\Models\Banner;
use OHM\Models\BannerDismissal;
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
    /* @var \PDOStatement */
    private $pdoStatement;
    /* @var Banner */
    private $banner;
    /* @var Banner */
    private $realBanner;

    function setUp(): void
    {
        $this->dbh = $this->createMock(\PDO::class);
        $this->pdoStatement = $this->createMock(\PDOStatement::class);
        $this->pdoStatement->method('rowCount')->willReturn(1);
        $this->pdoStatement->method('fetch')
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
        $this->dbh->method('prepare')->willReturn($this->pdoStatement);

        $this->realBanner = new Banner($this->dbh);
        $this->realBanner
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
        $this->banner->method('findEnabledAndAvailable')->willReturn([$this->realBanner]);

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

            $this->assertTrue($result,
                sprintf('banner should be displayed for students. (%d <= 15)', $userRights));
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

            $this->assertFalse($result,
                sprintf('banner should not be displayed for teachers. (%d > 15)', $userRights));
        }
    }

    /*
     * showBanner
     */

    public function testShowBanner(): void
    {
        $this->ohmBannerService
            ->setUserRights(1)
            ->setBannerForTesting($this->banner)
            ->setDisplayOnlyOncePerBanner(true);

        $result = $this->ohmBannerService->showBanners(OhmBannerService::TEACHER_ROLE);
        $this->assertTrue($result, 'the banner should be displayed.');
    }

    public function testShowBanner_TeacherBannerDisabled(): void
    {
        $this->realBanner->setDisplayTeacher(false);
        $this->ohmBannerService
            ->setUserRights(1)
            ->setBannerForTesting($this->banner)
            ->setDisplayOnlyOncePerBanner(true);

        $result = $this->ohmBannerService->showBanners(OhmBannerService::TEACHER_ROLE);
        $this->assertFalse($result, 'the teacher banner should NOT be displayed.');
    }

    public function testShowBanner_StudentBannerDisabled(): void
    {
        $this->realBanner->setDisplayStudent(false);
        $this->ohmBannerService
            ->setUserRights(1)
            ->setBannerForTesting($this->banner)
            ->setDisplayOnlyOncePerBanner(true);

        $result = $this->ohmBannerService->showBanners(OhmBannerService::STUDENT_ROLE);
        $this->assertFalse($result, 'the student banner should NOT be displayed.');
    }

    public function testShowBanner_BannerDismissed(): void
    {
        $bannerDismissal = $this->createMock(BannerDismissal::class);
        $bannerDismissal->method('getDismissedBannerIds')->willReturn([42]);

        $this->realBanner->setDisplayStudent(false);
        $this->ohmBannerService
            ->setUserRights(1)
            ->setBannerForTesting($this->banner)
            ->setBannerDismissalForTesting($bannerDismissal)
            ->setDisplayOnlyOncePerBanner(true);

        $result = $this->ohmBannerService->showBanners(OhmBannerService::STUDENT_ROLE);
        $this->assertFalse($result, 'the dismissed banner should NOT be displayed.');
    }

    public function testShowBanner_OnlyOnce(): void
    {
        $this->ohmBannerService
            ->setUserRights(1)
            ->setBannerForTesting($this->banner)
            ->setDisplayOnlyOncePerBanner(true);

        $result = $this->ohmBannerService->showBanners(OhmBannerService::TEACHER_ROLE);
        $this->assertTrue($result, 'the banner should be displayed on the first method call.');

        $result = $this->ohmBannerService->showBanners(OhmBannerService::TEACHER_ROLE);
        $this->assertFalse($result, 'the banner should be displayed only once.');
    }

    /*
     * previewBanner
     */

    public function testPreviewBanner_PreviewTeacher(): void
    {
        $this->ohmBannerService->setBannerForTesting($this->banner);
        $this->banner->method('find')->willReturn(true);

        $result = $this->ohmBannerService->previewBanner(42, OhmBannerService::TEACHER_ROLE);

        $this->assertTrue($result, 'the teacher banner preview should be displayed');
    }

    public function testPreviewBanner_PreviewStudent(): void
    {
        $this->ohmBannerService->setBannerForTesting($this->banner);
        $this->banner->method('find')->willReturn(true);

        $result = $this->ohmBannerService->previewBanner(42, OhmBannerService::STUDENT_ROLE);

        $this->assertTrue($result, 'the student banner preview should be displayed');
    }

    public function testPreviewBanner_BannerNotFound(): void
    {
        $this->pdoStatement->method('rowCount')->willReturn(0);
        $this->pdoStatement->method('fetch') ->willReturn(false);
        $this->ohmBannerService->setBannerForTesting($this->banner);

        $result = $this->ohmBannerService->previewBanner(123, OhmBannerService::STUDENT_ROLE);

        $this->assertFalse($result, 'the banner preview should NOT be displayed');
    }
}
