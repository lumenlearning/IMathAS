<?php

use PHPUnit\Framework\TestCase;


/*
 * These tests ensure certain original IMathAS macros still work after
 * loading OHM2 macros.
 */

final class ImathasMacrosTest extends TestCase
{
    static function setUpBeforeClass(): void
    {
        // These files contain macros as PHP functions instead of a class.
        require_once(__DIR__ . '/../../../../../../assessment/macros.php');
        require_once(__DIR__ . '/../../../../../../assessment/libs/ohm_macros.php');
    }

    public function testNumToWords(): void
    {
        $numberAsString = numtowords(5);
        $this->assertEquals('five', $numberAsString);

        $numberAsString = numtowords(200);
        $this->assertEquals('two hundred', $numberAsString);
    }
}
