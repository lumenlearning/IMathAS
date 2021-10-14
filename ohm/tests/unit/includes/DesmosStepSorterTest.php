<?php

namespace OHM\Tests;

use RuntimeException;
use Desmos\Includes\DesmosStepSorter;
use PHPUnit\Framework\TestCase;


/**
 * @covers DesmosStepSorter
 */
final class DesmosStepSorterTest extends TestCase
{
    /*
     * getUnsortedArrayIndexes
     */

    public function testGetUnsortedArrayIndexes(): void
    {
        $array = [5, 0, 100, 20, 15, 60];

        $result = DesmosStepSorter::getUnsortedArrayIndexes($array);
        $this->assertEquals([1, 0, 5, 3, 2, 4], $result);
    }

    /*
     * reorderByIndexes
     */

    public function testReorderByIndexes(): void
    {
        $referenceIndexes = [3, 6, 0, 2, 4, 9, 5, 8, 1, 7];
        $arrayToReorder = [11, 13, 14, 15, 16, 17, 18, 19, 123, 132];
        $expected = [15, 18, 11, 14, 16, 132, 17, 123, 13, 19];

        $result = DesmosStepSorter::reorderByIndexes($arrayToReorder, $referenceIndexes);
        $this->assertEquals($expected, $result);
    }

    public function testReorderByIndexes_SizeMismatch(): void
    {
        $this->expectException(RuntimeException::class);

        DesmosStepSorter::reorderByIndexes([0, 1], [0]);
    }
}
