<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Objects;

use Bula\Objects\TArrayList;
use Bula\Objects\TEnumerator;
use Bula\Objects\THashtable;
use Bula\Objects\TNull;

require_once("TArrayList.php");
require_once("TEnumerator.php");
require_once("THashtable.php");
require_once("TNull.php");

/**
 * Helper class for manipulating with arrays.
 */
class Arrays
{

    /**
     * Create new array of objects.
     * @param Integer $size Size of array.
     * @return Object[] Resulting array.
     */
    public static function newArray($size)
    {
        return array();
    }

    /**
     * Extend array with additional element.
     * @param Array $input Original array.
     * @param Object $element Object to add to original array.
     * @return Array Resulting array.
     */
    public static function extendArray($input, $element)
    {
        if ($input == null)
            return null;
        if ($element == null)
            return $input;

        $inputSize = SIZE($input);
        $newSize = $inputSize + 1;
        $output = self::newArray($newSize);
        for ($n = 0; $n < $inputSize; $n++)
            $output[$n] = $input[$n];
        $output[$inputSize] = $element;
        return $output;
    }

    /**
     * Create hash table from associative array.
     * @param Array $input Input array.
     * @return THashtable Resulting hash table.
     */
    public static function createTHashtable($input)
    {
        if ($input == null || !is_array($input))
            return null;
        $output = new THashtable();
        if (SIZE($input) == 0)
            return $output;

        $keys = Arrays::getArrayKeys($input);
        while ($keys->moveNext()) {
            $key = $keys->getCurrent();
            if (is_string($key)) {
                $value = Arrays::getArrayValue($input, $key);
                $output->put($key, NUL($value) ? TNull::getValue() : $value);
            }
        }
        return $output;
    }

    public static function getArrayKeys($input)
    {
        return new TEnumerator(array_keys($input));
    }

    public static function getArrayValue($input, $key)
    {
        return $input[$key];
    }
}
