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

    public function testGetHeaderSnippet_Disabled(): void
    {
        putenv('FULLSTORY_ENABLED'); // This unsets the environment variable.

        $result = $this->fullStory::getHeaderSnippet();
        $this->assertEmpty($result);
    }
}
