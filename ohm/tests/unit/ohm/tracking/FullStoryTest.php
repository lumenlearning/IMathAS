<?php

namespace OHM\Tests;

use OHM\Tracking\FullStory;
use PHPUnit\Framework\TestCase;


/**
 * @covers FullStory
 */
final class FullStoryTest extends TestCase
{
    /* @var FullStory */
    private $fullStory;

    function setUp(): void
    {
        $this->fullStory = new FullStory();

        putenv('FULLSTORY_ENABLED=true');

        $GLOBALS['configEnvironment'] = 'test';
        $GLOBALS['myrights'] = 10; // 10 = student
    }

    /*
     * isFullStoryEnabled
     */
    public function testIsFullStoryEnabled_Enabled(): void
    {
        $result = $this->fullStory::isFullStoryEnabled();
        $this->assertTrue($result);
    }

    public function testIsFullStoryEnabled_Disabled(): void
    {
        putenv('FULLSTORY_ENABLED'); // This unsets the environment variable.

        $result = $this->fullStory::isFullStoryEnabled();
        $this->assertFalse($result);
    }

    /*
     * isDebugMarkupEnabled
     */

    public function testIsDebugMarkupEnabled_Yes(): void
    {
        $GLOBALS['configEnvironment'] = 'test';
        $GLOBALS['myrights'] = 100;

        $result = $this->fullStory::isDebugMarkupEnabled();
        $this->assertTrue($result);
    }

    public function testIsDebugMarkupEnabled_Production(): void
    {
        $GLOBALS['configEnvironment'] = 'production';
        $GLOBALS['myrights'] = 100;

        $result = $this->fullStory::isDebugMarkupEnabled();
        $this->assertFalse($result);
    }

    public function testIsDebugMarkupEnabled_Unauthenticated(): void
    {
        $GLOBALS['configEnvironment'] = 'test';
        $GLOBALS['myrights'] = 0;

        $result = $this->fullStory::isDebugMarkupEnabled();
        $this->assertFalse($result);
    }

    public function testIsDebugMarkupEnabled_Invalid(): void
    {
        $GLOBALS['configEnvironment'] = 'test';
        $GLOBALS['myrights'] = '10'; // String values are considered invalid.

        $result = $this->fullStory::isDebugMarkupEnabled();
        $this->assertFalse($result);
    }

    /*
     * outputHeaderSnippet
     */

    public function testOutputHeaderSnippet_Enabled(): void
    {
        $result = $this->fullStory::outputHeaderSnippet();
        $this->assertTrue($result);
    }

    public function testOutputHeaderSnippet_Disabled(): void
    {
        putenv('FULLSTORY_ENABLED'); // This unsets the environment variable.

        $result = $this->fullStory::outputHeaderSnippet();
        $this->assertFalse($result);
    }

    /*
     * getHeaderSnippet
     */

    public function testGetHeaderSnippet_Enabled(): void
    {
        $result = $this->fullStory::getHeaderSnippet();
        $this->assertNotEmpty($result);
    }

    /*
     * getDebugMarkupSnippet
     */

    public function testGetDebugMarkupSnippet(): void
    {
        $result = $this->fullStory::getDebugMarkupSnippet();
        $this->assertNotEmpty($result);
    }

    /*
     * getUserMetadataSnippet
     */

    public function testGetUserMetadataFullStory(): void
    {
        $result = $this->fullStory::getUserMetadataSnippet();
        $this->assertNotEmpty($result);
    }

    public function testGetUserMetadataFullStory_RightsNotSet(): void
    {
        unset($GLOBALS['myrights']);

        $result = $this->fullStory::getUserMetadataSnippet();
        $this->assertEmpty($result);
    }

    public function testGetUserMetadataFullStory_NullRights(): void
    {
        $GLOBALS['myrights'] = null;

        $result = $this->fullStory::getUserMetadataSnippet();
        $this->assertEmpty($result);
    }

    public function testGetUserMetadataFullStory_EmptyRights(): void
    {
        $GLOBALS['myrights'] = '';

        $result = $this->fullStory::getUserMetadataSnippet();
        $this->assertEmpty($result);
    }

    public function testGetUserMetadataFullStory_Zero(): void
    {
        $GLOBALS['myrights'] = '0';

        $result = $this->fullStory::getUserMetadataSnippet();
        $this->assertEmpty($result);
    }

    /*
     * generateUserMetadata
     */

    public function testGenerateUserMetadata_NoRights1(): void
    {
        $GLOBALS['myrights'] = 0;

        $result = $this->fullStory::generateUserMetadata();
        $this->assertEmpty($result);
    }

    public function testGenerateUserMetadata_NoRights2(): void
    {
        $GLOBALS['myrights'] = null;

        $result = $this->fullStory::generateUserMetadata();
        $this->assertEmpty($result);
    }

    public function testGenerateUserMetadata_NoRights3(): void
    {
        unset($GLOBALS['myrights']);

        $result = $this->fullStory::generateUserMetadata();
        $this->assertEmpty($result);
    }

    public function testGenerateUserMetadata(): void
    {
        $GLOBALS['myrights'] = 10;
        $GLOBALS['userid'] = 1138;
        $GLOBALS['groupid'] = 42;
        $GLOBALS['cid'] = 123;
        $GLOBALS['coursename'] = 'How to Meow';
        $GLOBALS['ohmEnrollmentId'] = 666;
        $GLOBALS['ohmCourseTeacherId'] = 543;

        $result = $this->fullStory::generateUserMetadata();
        $this->assertEquals('ohm', $result['product_str']);
        $this->assertEquals('OHM-1138', $result['userId_str']);
        $this->assertEquals('student', $result['role_str']);
        $this->assertEquals(42, $result['groupId_int']);
        $this->assertEquals(123, $result['courseId_int']);
        $this->assertEquals('How to Meow', $result['courseName_str']);
        $this->assertEquals('OHM-543', $result['instructorId_str']);
        $this->assertEquals('OHM-666', $result['enrollmentId_str']);
    }

    /*
     * getUserRole
     */

    public function testgetUserRole(): void
    {
        $GLOBALS['myrights'] = 1;
        $role = $this->fullStory::getUserRole();
        $this->assertEquals('unknown', $role);

        $GLOBALS['myrights'] = 666;
        $role = $this->fullStory::getUserRole();
        $this->assertEquals('unknown', $role);

        $GLOBALS['myrights'] = 5;
        $role = $this->fullStory::getUserRole();
        $this->assertEquals('guest', $role);

        $GLOBALS['myrights'] = 10;
        $role = $this->fullStory::getUserRole();
        $this->assertEquals('student', $role);

        $GLOBALS['myrights'] = 12;
        $role = $this->fullStory::getUserRole();
        $this->assertEquals('pending-approval', $role);

        $GLOBALS['myrights'] = 15;
        $role = $this->fullStory::getUserRole();
        $this->assertEquals('tutor', $role);

        $GLOBALS['myrights'] = 20;
        $role = $this->fullStory::getUserRole();
        $this->assertEquals('instructor', $role);

        $GLOBALS['myrights'] = 40;
        $role = $this->fullStory::getUserRole();
        $this->assertEquals('limited-course-creator', $role);

        $GLOBALS['myrights'] = 75;
        $role = $this->fullStory::getUserRole();
        $this->assertEquals('group-admin', $role);

        $GLOBALS['myrights'] = 100;
        $role = $this->fullStory::getUserRole();
        $this->assertEquals('administrator', $role);
    }
}
