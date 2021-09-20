<?php

namespace Desmos\Includes;

use RuntimeException;

class DesmosStepSorter
{
    /**
     * Get indexes for unsorted array.
     *
     * Example unsorted array: [5,0,20,15]
     * Expected return value : [1,0,3,2]
     *
     * Based on code from CPallini:
     * https://www.codeproject.com/questions/147988/get-sorted-order-indices-from-an-unsorted-array
     *
     * @param array $array int The unsorted array.
     * @return array An array of indexes.
     */
    public static function getUnsortedArrayIndexes(array $array): array
    {
        $arraySize = sizeof($array);

        $indexes = [];
        for ($i = 0; $i < $arraySize; $i++) {
            $indexes[$i] = -1;
            for ($j = 0; $j < $arraySize; $j++) {
                if ($array[$i] >= $array[$j]) {
                    $indexes[$i]++;
                }
            }
        }

        return $indexes;
    }

    /**
     * Reorder an array according to provided indexes.
     *
     * Example arguments    : [11,22,33,44], [1,0,4,3]
     * Expected return value: [22,11,44,33]
     *
     * @param array $arrayToReorder The array to reorder.
     * @param array $indexes An array of indexes.
     * @return array
     * @throws RuntimeException Thrown if $arrayToReorder size doesn't match $indexes size.
     */
    public static function reorderByIndexes(array $arrayToReorder, array $indexes): array
    {
        $arraySize = sizeof($arrayToReorder);
        $indexCount = sizeof($indexes);

        if ($arraySize !== $indexCount) {
            throw new RuntimeException("Array size does not match the number of provided indexes.");
        }

        // Create a new array with elements indexed properly.
        $newOrderedArray = $arrayToReorder;
        for ($i = 0; $i < $arraySize; $i++) {
            $value = $arrayToReorder[$i];
            $newIndex = array_search($i, $indexes);

            $newOrderedArray[$newIndex] = $value;
        }

        return $newOrderedArray;
    }
}
