<?php

namespace OHM\Tests;

use OHM\Controlers\Assessments;
use PHPUnit\Framework\TestCase;


/**
 * @covers \OHM\Controlers\Assessments
 */
final class AssessmentsTest extends TestCase
{
    private $assessments;

    function setUp(): void
    {
        $this->assessments = new Assessments();
    }

    /*
     * getScoreForDisplay
     */

    public function testGetScoreForDisplay_V1(): void
    {
        $bestscores = '0,1,1;0,1,1;0,1,1';
        $score = $this->assessments::getScoreForDisplay($bestscores);
        $this->assertEquals(2, $score);
    }

    public function testGetScoreForDisplay_V1_multipart(): void
    {
        $bestscores = '0.5~0.5,0.25~0.25~0.25~0.25;1~1,1~1~1~1';
        $score = $this->assessments::getScoreForDisplay($bestscores);
        $this->assertEquals(2, $score);
    }

    public function testGetScoreForDisplay_V2(): void
    {
        $bestscores = '2.00';
        $score = $this->assessments::getScoreForDisplay($bestscores);
        $this->assertEquals(2, $score);
    }
}
