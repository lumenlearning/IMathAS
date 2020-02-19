<?php

namespace OHM\Tests;

use OHM\Includes\OhmBanner;
use PHPUnit\Framework\TestCase;


/**
 * @covers OhmBanner
 */
final class OhmBannerTest extends TestCase
{
    const EMPTY_VIEW_FILE = '/ohm/tests/fixtures/empty.php';

    private $ohmBanner;

    function setUp(): void
    {
        $this->ohmBanner = new OhmBanner(0);
    }

    /*
     * showTeacherBannerForTeachersOnly
     */

    public function testShowTeacherBannerForTeachersOnly_UserIsTeacher(): void
    {
        $this->ohmBanner->setEnv([OhmBanner::ENV_TEACHER_FILENAME_KEY => '  ' . self::EMPTY_VIEW_FILE . '  ']);

        foreach ([20, 40, 75, 100] as $userRights) {
            $this->ohmBanner->setUserRights($userRights);

            $result = $this->ohmBanner->showTeacherBannerForTeachersOnly();

            $this->assertTrue($result, 'banner should be displayed for teachers. (user rights > 15)');
        }
    }

    public function testShowTeacherBannerForTeachersOnly_UserIsNotTeacher(): void
    {
        $this->ohmBanner->setEnv([OhmBanner::ENV_TEACHER_FILENAME_KEY => '  ' . self::EMPTY_VIEW_FILE . '  ']);

        foreach ([0, 5, 10, 12, 15] as $userRights) {
            $this->ohmBanner->setUserRights($userRights);

            $result = $this->ohmBanner->showTeacherBannerForTeachersOnly();

            $this->assertFalse($result, 'banner should not be displayed for students. (user rights <= 15)');
        }
    }

    /*
     * showStudentBannerForStudentsOnly
     */

    public function testShowStudentBannerForStudentsOnly_UserIsStudent(): void
    {
        $this->ohmBanner->setEnv([OhmBanner::ENV_STUDENT_FILENAME_KEY => '  ' . self::EMPTY_VIEW_FILE . '  ']);

        foreach ([0, 5, 10, 12, 15] as $userRights) {
            $this->ohmBanner->setUserRights($userRights);

            $result = $this->ohmBanner->showStudentBannerForStudentsOnly();

            $this->assertTrue($result, 'banner should be displayed for students. (user rights <= 15)');
        }
    }

    public function testShowStudentBannerForStudentsOnly_UserIsNotStudent(): void
    {
        $this->ohmBanner->setEnv([OhmBanner::ENV_STUDENT_FILENAME_KEY => '  ' . self::EMPTY_VIEW_FILE . '  ']);

        foreach ([20, 40, 75, 100] as $userRights) {
            $this->ohmBanner->setUserRights($userRights);

            $result = $this->ohmBanner->showStudentBannerForStudentsOnly();

            $this->assertFalse($result, 'banner should not be displayed for teachers. (user rights > 15)');
        }
    }

    /*
     * showTeacherBanner
     */

    public function testShowTeacherBanner_ViewFileExists(): void
    {
        // Surrounding empty spaces should be trimmed by method under test.
        $this->ohmBanner->setEnv([OhmBanner::ENV_TEACHER_FILENAME_KEY => '  ' . self::EMPTY_VIEW_FILE . '  ']);

        $result = $this->ohmBanner->showTeacherBanner();

        $this->assertTrue($result, 'banner should be displayed if view file exists.');
    }

    public function testShowTeacherBanner_ViewFileMissing(): void
    {
        $this->ohmBanner->setEnv([OhmBanner::ENV_TEACHER_FILENAME_KEY => 'f56d30ce']);

        $result = $this->ohmBanner->showTeacherBanner();

        $this->assertFalse($result, 'banner should not be displayed if view file is missing.');
    }

    public function testShowTeacherBanner_ViewFileNotSpecified(): void
    {
        $result = $this->ohmBanner->showTeacherBanner();

        $this->assertFalse($result, 'banner should not be displayed if view file is not specified.');
    }

    public function testShowTeacherBanner_ViewFilenameIsEmptyString(): void
    {
        // Surrounding empty spaces should be trimmed by method under test.
        $this->ohmBanner->setEnv([OhmBanner::ENV_TEACHER_FILENAME_KEY => '   ']);

        $result = $this->ohmBanner->showTeacherBanner();

        $this->assertFalse($result, 'banner should not be displayed if view filename is an empty string.');
    }

    /*
     * showStudentBanner
     */

    public function testShowStudentBanner_ViewFileExists(): void
    {
        // Surrounding empty spaces should be trimmed by method under test.
        $this->ohmBanner->setEnv([OhmBanner::ENV_STUDENT_FILENAME_KEY => '  ' . self::EMPTY_VIEW_FILE . '  ']);

        $result = $this->ohmBanner->showStudentBanner();

        $this->assertTrue($result, 'banner should be displayed if view file exists.');
    }

    public function testShowStudentBanner_ViewFileMissing(): void
    {
        $this->ohmBanner->setEnv([OhmBanner::ENV_STUDENT_FILENAME_KEY => 'f56d30ce']);

        $result = $this->ohmBanner->showStudentBanner();

        $this->assertFalse($result, 'banner should not be displayed if view file is missing.');
    }

    public function testShowStudentBanner_ViewFileNotSpecified(): void
    {
        $result = $this->ohmBanner->showStudentBanner();

        $this->assertFalse($result, 'banner should not be displayed if view file is not specified.');
    }

    public function testShowStudentBanner_ViewFilenameIsEmptyString(): void
    {
        // Surrounding empty spaces should be trimmed by method under test.
        $this->ohmBanner->setEnv([OhmBanner::ENV_STUDENT_FILENAME_KEY => '   ']);

        $result = $this->ohmBanner->showStudentBanner();

        $this->assertFalse($result, 'banner should not be displayed if view filename is an empty string.');
    }
}
