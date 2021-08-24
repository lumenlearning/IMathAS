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
    public function testIsFullStoryEnabled_Enabled(): void
    {
        $result = FullStory::isFullStoryEnabled();
        $this->assertTrue($result);
    }

    public function testIsFullStoryEnabled_Disabled(): void
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
