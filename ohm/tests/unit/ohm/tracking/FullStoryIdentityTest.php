<?php

namespace OHM\Tests;

use OHM\Tracking\FullStoryIdentity;
use PHPUnit\Framework\TestCase;

/**
 * @covers FullStoryIdentity
 */
final class FullStoryIdentityTest extends TestCase
{
    function setUp(): void
    {
        putenv('FULLSTORY_ENABLED=true');

        unset($_SESSION);

        $GLOBALS['configEnvironment'] = 'test';
        $GLOBALS['myrights'] = 10; // 10 = student
        $_SERVER['REQUEST_URI'] = '/'; // userNeedsFullStoryIdentity() checks for URLs to exclude.
    }

    function tearDown(): void
    {
        unset($_SESSION);
        unset($GLOBALS['userid']);
    }

    /*
     * getRealUserId
     */

    public function testGetRealUserId(): void
    {
        $GLOBALS['userid'] = 42;

        $result = FullStoryIdentity::getRealUserId();
        $this->assertEquals(42, $result);
    }

    public function testGetRealUserId_Impersonator(): void
    {
        $GLOBALS['userid'] = 123;
        $_SESSION['emulateuseroriginaluser'] = 42;

        $result = FullStoryIdentity::getRealUserId();
        $this->assertEquals(42, $result);
    }

    public function testGetRealUserId_NoUser(): void
    {
        $result = FullStoryIdentity::getRealUserId();
        $this->assertNull($result);
    }

    public function testGetRealUserId_InvalidUser(): void
    {
        $GLOBALS['userid'] = 0;

        $result = FullStoryIdentity::getRealUserId();
        $this->assertNull($result);
    }

    /*
     * getImpostorUserId
     */

    public function testGetImpostorUserId(): void
    {
        $_SESSION['emulateuseroriginaluser'] = 3417;

        $result = FullStoryIdentity::getImpostorUserId();
        $this->assertEquals(3417, $result);
    }

    public function testGetImpostorUserId_None(): void
    {
        $result = FullStoryIdentity::getImpostorUserId();
        $this->assertNull($result);
    }

    /*
     * userNeedsFullStoryIdentity
     */

    public function testUserNeedsFullStoryIdentity_NewSession(): void
    {
        $GLOBALS['userid'] = 64;

        $result = FullStoryIdentity::userNeedsFullStoryIdentity();
        $this->assertTrue($result);
    }

    public function testUserNeedsFullStoryIdentity_NeverIdentified(): void
    {
        $GLOBALS['userid'] = 64;
        $_SESSION['meow'] = 'lol';

        $result = FullStoryIdentity::userNeedsFullStoryIdentity();
        $this->assertTrue($result);
    }

    public function testUserNeedsFullStoryIdentity_SamePhpSession_NewUser(): void
    {
        $GLOBALS['userid'] = 64;
        $_SESSION['sent-fullstory-user-identity'] = 128;

        $result = FullStoryIdentity::userNeedsFullStoryIdentity();
        $this->assertTrue($result);
    }

    public function testUserNeedsFullStoryIdentity_SamePhpSession_SameUser(): void
    {
        $GLOBALS['userid'] = 64;
        $_SESSION['sent-fullstory-user-identity'] = 64;

        $result = FullStoryIdentity::userNeedsFullStoryIdentity();
        $this->assertFalse($result);
    }

    public function testUserNeedsFullStoryIdentity_ExcludedUrl(): void
    {
        $GLOBALS['userid'] = 64;
        $_SERVER['REQUEST_URI'] = FullStoryIdentity::EXCLUDE_URL_PATHS[0];

        $result = FullStoryIdentity::userNeedsFullStoryIdentity();
        $this->assertFalse($result);
    }

    /*
     * getUserRole
     */

    public function testGetUserRole(): void
    {
        $GLOBALS['myrights'] = 1;
        $role = FullStoryIdentity::getUserRole();
        $this->assertEquals('unknown', $role);

        $GLOBALS['myrights'] = 666;
        $role = FullStoryIdentity::getUserRole();
        $this->assertEquals('unknown', $role);

        $GLOBALS['myrights'] = 5;
        $role = FullStoryIdentity::getUserRole();
        $this->assertEquals('guest', $role);

        $GLOBALS['myrights'] = 10;
        $role = FullStoryIdentity::getUserRole();
        $this->assertEquals('student', $role);

        $GLOBALS['myrights'] = 12;
        $role = FullStoryIdentity::getUserRole();
        $this->assertEquals('pending-approval', $role);

        $GLOBALS['myrights'] = 15;
        $role = FullStoryIdentity::getUserRole();
        $this->assertEquals('tutor', $role);

        $GLOBALS['myrights'] = 20;
        $role = FullStoryIdentity::getUserRole();
        $this->assertEquals('instructor', $role);

        $GLOBALS['myrights'] = 40;
        $role = FullStoryIdentity::getUserRole();
        $this->assertEquals('limited-course-creator', $role);

        $GLOBALS['myrights'] = 75;
        $role = FullStoryIdentity::getUserRole();
        $this->assertEquals('group-admin', $role);

        $GLOBALS['myrights'] = 100;
        $role = FullStoryIdentity::getUserRole();
        $this->assertEquals('administrator', $role);
    }

    public function testGetUserRole_Specified(): void
    {
        $GLOBALS['myrights'] = -1;
        $role = FullStoryIdentity::getUserRole(1);
        $this->assertEquals('unknown', $role);

        $GLOBALS['myrights'] = -1;
        $role = FullStoryIdentity::getUserRole(666);
        $this->assertEquals('unknown', $role);

        $GLOBALS['myrights'] = -1;
        $role = FullStoryIdentity::getUserRole(5);
        $this->assertEquals('guest', $role);

        $GLOBALS['myrights'] = -10;
        $role = FullStoryIdentity::getUserRole(10);
        $this->assertEquals('student', $role);

        $GLOBALS['myrights'] = -12;
        $role = FullStoryIdentity::getUserRole(12);
        $this->assertEquals('pending-approval', $role);

        $GLOBALS['myrights'] = -15;
        $role = FullStoryIdentity::getUserRole(15);
        $this->assertEquals('tutor', $role);

        $GLOBALS['myrights'] = -20;
        $role = FullStoryIdentity::getUserRole(20);
        $this->assertEquals('instructor', $role);

        $GLOBALS['myrights'] = -40;
        $role = FullStoryIdentity::getUserRole(40);
        $this->assertEquals('limited-course-creator', $role);

        $GLOBALS['myrights'] = -75;
        $role = FullStoryIdentity::getUserRole(75);
        $this->assertEquals('group-admin', $role);

        $GLOBALS['myrights'] = -100;
        $role = FullStoryIdentity::getUserRole(100);
        $this->assertEquals('administrator', $role);
    }
}
