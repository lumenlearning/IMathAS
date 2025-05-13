<?php

namespace OHM\Tests;

use OHM\Tracking\FullStory;
use PHPUnit\Framework\TestCase;

/**
 * @covers FullStory
 */
final class FullStoryTest extends TestCase
{
    function setUp(): void
    {
        putenv('FULLSTORY_ENABLED=true');

        unset($_SESSION);

        $GLOBALS['configEnvironment'] = 'test';
        $GLOBALS['myrights'] = 10; // 10 = student
    }

    function tearDown(): void
    {
        unset($_SESSION);
        unset($GLOBALS['userid']);
    }

    /*
     * isFullStoryEnabled
     */

    public function testIsFullStoryEnabled_Enabled_NoMode(): void
    {
        putenv('FULLSTORY_ENABLED=true');
        putenv('FULLSTORY_MODE');   // This unsets the environment variable.

        $result = FullStory::isFullStoryEnabled();
        $this->assertTrue($result);
    }

    public function testIsFullStoryEnabled_Enabled_InvalidMode(): void
    {
        putenv('FULLSTORY_ENABLED=true');
        putenv('FULLSTORY_MODE=asdf');

        $result = FullStory::isFullStoryEnabled();
        $this->assertFalse($result);
    }

    public function testIsFullStoryEnabled_Enabled_Everyone(): void
    {
        putenv('FULLSTORY_ENABLED=true');
        putenv('FULLSTORY_MODE=everyone');

        $result = FullStory::isFullStoryEnabled();
        $this->assertTrue($result);
    }

    /*
     * isFullStoryEnabled (for educators)
     */

    public function testIsFullStoryEnabled_Enabled_Educators_UserIsPendingApproval(): void
    {
        putenv('FULLSTORY_ENABLED=true');
        putenv('FULLSTORY_MODE=educators');

        $GLOBALS['myrights'] = 12;

        $result = FullStory::isFullStoryEnabled();
        $this->assertTrue($result);
    }

    public function testIsFullStoryEnabled_Enabled_Educators_UserIsInstructor(): void
    {
        putenv('FULLSTORY_ENABLED=true');
        putenv('FULLSTORY_MODE=educators');

        $GLOBALS['myrights'] = 20;

        $result = FullStory::isFullStoryEnabled();
        $this->assertTrue($result);
    }

    public function testIsFullStoryEnabled_Enabled_Educators_UserIsLimitedCourseCreator(): void
    {
        putenv('FULLSTORY_ENABLED=true');
        putenv('FULLSTORY_MODE=educators');

        $GLOBALS['myrights'] = 40;

        $result = FullStory::isFullStoryEnabled();
        $this->assertTrue($result);
    }

    public function testIsFullStoryEnabled_Enabled_Educators_UserIsGroupAdmin(): void
    {
        putenv('FULLSTORY_ENABLED=true');
        putenv('FULLSTORY_MODE=educators');

        $GLOBALS['myrights'] = 75;

        $result = FullStory::isFullStoryEnabled();
        $this->assertTrue($result);
    }

    public function testIsFullStoryEnabled_Enabled_Educators_UserIsGuest(): void
    {
        putenv('FULLSTORY_ENABLED=true');
        putenv('FULLSTORY_MODE=educators');

        $GLOBALS['myrights'] = 5;

        $result = FullStory::isFullStoryEnabled();
        $this->assertFalse($result);
    }

    public function testIsFullStoryEnabled_Enabled_Educators_UserIsStudent(): void
    {
        putenv('FULLSTORY_ENABLED=true');
        putenv('FULLSTORY_MODE=educators');

        $GLOBALS['myrights'] = 10;

        $result = FullStory::isFullStoryEnabled();
        $this->assertFalse($result);
    }

    public function testIsFullStoryEnabled_Enabled_Educators_UserIsTutor(): void
    {
        putenv('FULLSTORY_ENABLED=true');
        putenv('FULLSTORY_MODE=educators');

        $GLOBALS['myrights'] = 15;

        $result = FullStory::isFullStoryEnabled();
        $this->assertFalse($result);
    }

    /*
     * isFullStoryEnabled (for students)
     */

    public function testIsFullStoryEnabled_Enabled_Students_UserIsStudent(): void
    {
        putenv('FULLSTORY_ENABLED=true');
        putenv('FULLSTORY_MODE=students');

        $GLOBALS['myrights'] = 10;

        $result = FullStory::isFullStoryEnabled();
        $this->assertTrue($result);
    }

    public function testIsFullStoryEnabled_Enabled_Students_UserIsTutor(): void
    {
        putenv('FULLSTORY_ENABLED=true');
        putenv('FULLSTORY_MODE=students');

        $GLOBALS['myrights'] = 15;

        $result = FullStory::isFullStoryEnabled();
        $this->assertTrue($result);
    }

    public function testIsFullStoryEnabled_Enabled_Students_UserIsPendingApproval(): void
    {
        putenv('FULLSTORY_ENABLED=true');
        putenv('FULLSTORY_MODE=students');

        $GLOBALS['myrights'] = 12;

        $result = FullStory::isFullStoryEnabled();
        $this->assertFalse($result);
    }

    public function testIsFullStoryEnabled_Enabled_Students_UserIsInstructor(): void
    {
        putenv('FULLSTORY_ENABLED=true');
        putenv('FULLSTORY_MODE=students');

        $GLOBALS['myrights'] = 20;

        $result = FullStory::isFullStoryEnabled();
        $this->assertFalse($result);
    }

    public function testIsFullStoryEnabled_Enabled_Students_UserIsLimitedCourseCreator(): void
    {
        putenv('FULLSTORY_ENABLED=true');
        putenv('FULLSTORY_MODE=students');

        $GLOBALS['myrights'] = 40;

        $result = FullStory::isFullStoryEnabled();
        $this->assertFalse($result);
    }

    public function testIsFullStoryEnabled_Enabled_Students_UserIsGroupAdmin(): void
    {
        putenv('FULLSTORY_ENABLED=true');
        putenv('FULLSTORY_MODE=students');

        $GLOBALS['myrights'] = 75;

        $result = FullStory::isFullStoryEnabled();
        $this->assertFalse($result);
    }

    /*
     * isFullStoryEnabled (disabled)
     */

    public function testIsFullStoryEnabled_Disabled(): void
    {
        putenv('FULLSTORY_ENABLED=false');

        $result = FullStory::isFullStoryEnabled();
        $this->assertFalse($result);
    }

    public function testIsFullStoryEnabled_InvalidValue(): void
    {
        putenv('FULLSTORY_ENABLED=asdf');

        $result = FullStory::isFullStoryEnabled();
        $this->assertFalse($result);
    }

    public function testIsFullStoryEnabled_NotSet(): void
    {
        putenv('FULLSTORY_ENABLED'); // This unsets the environment variable.

        $result = FullStory::isFullStoryEnabled();
        $this->assertFalse($result);
    }

    /*
     * isDebugMarkupEnabled
     */

    public function testIsDebugMarkupEnabled_Yes(): void
    {
        $GLOBALS['configEnvironment'] = 'test';
        $GLOBALS['myrights'] = 100;

        $result = FullStory::isDebugMarkupEnabled();
        $this->assertTrue($result);
    }

    public function testIsDebugMarkupEnabled_Production(): void
    {
        $GLOBALS['configEnvironment'] = 'production';
        $GLOBALS['myrights'] = 100;

        $result = FullStory::isDebugMarkupEnabled();
        $this->assertFalse($result);
    }

    public function testIsDebugMarkupEnabled_Unauthenticated(): void
    {
        $GLOBALS['configEnvironment'] = 'test';
        $GLOBALS['myrights'] = 0;

        $result = FullStory::isDebugMarkupEnabled();
        $this->assertFalse($result);
    }

    public function testIsDebugMarkupEnabled_Invalid(): void
    {
        $GLOBALS['configEnvironment'] = 'test';
        $GLOBALS['myrights'] = '10'; // String values are considered invalid.

        $result = FullStory::isDebugMarkupEnabled();
        $this->assertFalse($result);
    }

    /*
     * outputHeaderSnippet
     */

    public function testOutputHeaderSnippet_Enabled(): void
    {
        $result = FullStory::outputHeaderSnippet();
        $this->assertTrue($result);
    }

    public function testOutputHeaderSnippet_Disabled(): void
    {
        putenv('FULLSTORY_ENABLED'); // This unsets the environment variable.

        $result = FullStory::outputHeaderSnippet();
        $this->assertFalse($result);
    }
}
