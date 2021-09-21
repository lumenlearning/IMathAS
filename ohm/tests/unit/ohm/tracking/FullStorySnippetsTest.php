<?php

namespace OHM\Tests;

use OHM\Tracking\FullStorySnippets;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

/**
 * @covers FullStorySnippets
 */
final class FullStorySnippetsTest extends TestCase
{
    function setUp(): void
    {
        putenv('FULLSTORY_ENABLED=true');

        unset($_SESSION);

        $GLOBALS['configEnvironment'] = 'test';
        $GLOBALS['myrights'] = 10; // 10 = student

        $GLOBALS['DBH'] = $this->createMock(PDO::class);
    }

    function tearDown(): void
    {
        unset($_SESSION);
        unset($GLOBALS['userid']);
    }

    /*
     * getHeaderSnippet
     */

    public function testGetHeaderSnippet_Enabled(): void
    {
        $result = FullStorySnippets::getHeaderSnippet();
        $this->assertNotEmpty($result);
    }

    /*
     * getDebugMarkupSnippet
     */

    public function testGetDebugMarkupSnippet(): void
    {
        $result = FullStorySnippets::getDebugMarkupSnippet();
        $this->assertNotEmpty($result);
    }

    /*
     * getCurrentUserIdentitySnippet
     */

    public function testGetCurrentUserIdentitySnippet(): void
    {
        $GLOBALS['userid'] = 42;
        $_SESSION['need-fullstory-user-identity'] = true;

        $result = FullStorySnippets::getCurrentUserIdentitySnippet();
        $this->assertStringContainsString('OHM-42', $result);
    }

    public function testGetCurrentUserIdentitySnippet_NoUser(): void
    {
        $_SESSION['need-fullstory-user-identity'] = true;

        $result = FullStorySnippets::getCurrentUserIdentitySnippet();
        $this->assertEmpty($result);
    }

    public function testGetCurrentUserIdentitySnippet_InvalidUser(): void
    {
        $GLOBALS['userid'] = 0;
        $_SESSION['need-fullstory-user-identity'] = true;

        $result = FullStorySnippets::getCurrentUserIdentitySnippet();
        $this->assertEmpty($result);
    }

    /*
     * getCurrentUserMetadataSnippet
     */

    public function testGetUserMetadataSnippet(): void
    {
        $result = FullStorySnippets::getCurrentUserMetadataSnippet();
        $this->assertNotEmpty($result);
    }

    public function testGetUserMetadataSnippet_RightsNotSet(): void
    {
        unset($GLOBALS['myrights']);

        $result = FullStorySnippets::getCurrentUserMetadataSnippet();
        $this->assertEmpty($result);
    }

    public function testGetUserMetadataSnippet_NullRights(): void
    {
        $GLOBALS['myrights'] = null;

        $result = FullStorySnippets::getCurrentUserMetadataSnippet();
        $this->assertEmpty($result);
    }

    public function testGetUserMetadataSnippet_EmptyRights(): void
    {
        $GLOBALS['myrights'] = '';

        $result = FullStorySnippets::getCurrentUserMetadataSnippet();
        $this->assertEmpty($result);
    }

    public function testGetUserMetadataSnippet_Zero(): void
    {
        $GLOBALS['myrights'] = '0';

        $result = FullStorySnippets::getCurrentUserMetadataSnippet();
        $this->assertEmpty($result);
    }

    /*
     * generateLoggedInUserMetadata
     */

    public function testGenerateLoggedInUserMetadata_Globals(): void
    {
        $GLOBALS['userid'] = 1138;
        $GLOBALS['groupid'] = 123;
        $GLOBALS['myrights'] = 10;

        $result = FullStorySnippets::generateLoggedInUserMetadata();
        $this->assertEquals('OHM-1138', $result['userId_str']);
        $this->assertEquals('student', $result['role_str']);
        $this->assertEquals(123, $result['groupId_int']);
    }

    public function testGenerateLoggedInUserMetadata_Impostor(): void
    {
        $_SESSION['emulateuseroriginaluser'] = 3417;

        // These two values should not be returned even when they exist.
        $GLOBALS['userid'] = 5241;
        $GLOBALS['groupid'] = 123;
        $GLOBALS['myrights'] = 8192;

        // When emulating a user, the logged in user's info should be
        // fetched from the database while ignoring globals.
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->method('fetch')->willReturn(['rights' => 20, 'groupid' => 42]);
        $GLOBALS['DBH']->method('prepare')->willReturn($pdoStatement);

        $result = FullStorySnippets::generateLoggedInUserMetadata();
        $this->assertEquals('OHM-3417', $result['userId_str']);
        $this->assertEquals('instructor', $result['role_str']);
        $this->assertEquals(42, $result['groupId_int']);
    }

    /*
     * generateUserMetadataByGlobals
     */

    public function testGenerateUserMetadataByGlobals_NoRights1(): void
    {
        $GLOBALS['myrights'] = 0;

        $result = FullStorySnippets::generateUserMetadataByGlobals();
        $this->assertEmpty($result);
    }

    public function testGenerateUserMetadataByGlobals_NoRights2(): void
    {
        $GLOBALS['myrights'] = null;

        $result = FullStorySnippets::generateUserMetadataByGlobals();
        $this->assertEmpty($result);
    }

    public function testGenerateUserMetadataByGlobals_NoRights3(): void
    {
        unset($GLOBALS['myrights']);

        $result = FullStorySnippets::generateUserMetadataByGlobals();
        $this->assertEmpty($result);
    }

    public function testGenerateUserMetadataByGlobals(): void
    {
        $GLOBALS['myrights'] = 10;
        $GLOBALS['userid'] = 1138;
        $GLOBALS['groupid'] = 42;
        $GLOBALS['cid'] = 123;
        $GLOBALS['coursename'] = 'How to Meow';
        $GLOBALS['ohmEnrollmentId'] = 666;
        $GLOBALS['ohmCourseTeacherId'] = 543;

        $result = FullStorySnippets::generateUserMetadataByGlobals();
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
     * generateUserMetadataByUserId
     */

    public function testGenerateUserMetadataByUserId(): void
    {
        // The following values should not be returned even when they exist.
        $GLOBALS['myrights'] = 8192;
        $GLOBALS['userid'] = 5241;
        $GLOBALS['groupid'] = 64;
        $GLOBALS['ohmEnrollmentId'] = 666;
        // These values should be returned.
        $GLOBALS['cid'] = 123;
        $GLOBALS['coursename'] = 'How to Meow';
        $GLOBALS['ohmCourseTeacherId'] = 543;

        // When emulating a user, the logged in user's info should be
        // fetched from the database while ignoring globals.
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->method('fetch')->willReturn(['rights' => 20, 'groupid' => 2048]);
        $GLOBALS['DBH']->method('prepare')->willReturn($pdoStatement);

        $result = FullStorySnippets::generateUserMetadataByUserId(3417);
        $this->assertEquals('ohm', $result['product_str']);
        $this->assertEquals('OHM-3417', $result['userId_str']);
        $this->assertEquals('instructor', $result['role_str']);
        $this->assertEquals(2048, $result['groupId_int']);
        $this->assertEquals(123, $result['courseId_int']);
        $this->assertEquals('How to Meow', $result['courseName_str']);
        $this->assertEquals('OHM-543', $result['instructorId_str']);
    }
}
