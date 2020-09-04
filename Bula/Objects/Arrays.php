<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Objects;

use Bula\Objects\ArrayList;
use Bula\Objects\Hashtable;

/**
 * Helper class for manipulating with arrays.
 */
class Arrays {
    /** Create new array list. */
    public static function newArrayList() {
        return new ArrayList();
    }

    /**
     * Create new hash table.
     * @return Hashtable New hash table.
     */
    public static function newHashtable() {
        return new Hashtable();
    }

    /**
     * Create new array of objects.
     * @param Integer $size Size of array.
     * @return Object[] Resulting array.
     */
    public static function newArray($size) {
        return array();
    }

    /**
     * Merge hash tables.
     * @param Hashtable $input Original hash table.
     * @param Hashtable $extra Hash table to merge with original one.
     * @return Hashtable Merged hash table.
     */
    public static function mergeHashtable($input, $extra) {
        if ($input == null)
            return null;
        if ($extra == null)
            return $input;

        $output = /*(Hashtable)*/$input->cloneMe();
        $keys = $extra->keys();
        while ($keys->moveNext()) {
            $key = /*(TString)*/$keys->current();
            $output->put($key, $extra->get($key));
        }
        return $output;
    }

    /**
     * Merge array lists.
     * @param ArrayList $input Original array list.
     * @param ArrayList $extra Array list to merge with original one.
     * @return ArrayList Resulting array list.
     */
    public static function mergeArrayList($input, $extra) {
        if ($input == null)
            return null;
        if ($extra == null)
            return $input;

        $output = self::newArrayList();
        for ($n = 0; $n < SIZE($input); $n++)
            $output->add($input->get($n));
        for ($n = 0; $n < SIZE($extra); $n++)
            $output->add($extra->get($n));
        return $output;
    }

    /**
     * Merge arrays.
     * @param Array $input Original array.
     * @param Array $extra Array to merge with original one.
     * @return Array Resulting array.
     */
    public static function mergeArray($input, $extra) {
        if ($input == null)
            return null;
        if ($extra == null)
            return $input;

        $input_size = SIZE($input);
        $extra_size = SIZE($extra);
        $new_size = $input_size + $extra_size;
        $output = self::newArray($new_size);
        for ($n = 0; $n < $input_size; $n++)
            $output[$n] = $input[$n];
        for ($n = 0; $n < $extra_size; $n++)
            $output[$input_size + $n] = $extra[$n];
        return $output;
    }

    /**
     * Extend array with additional element.
     * @param Array $input Original array.
     * @param Object $element Object to add to original array.
     * @return Array Resulting array.
     */
    public static function extendArray($input, $element) {
        if ($input == null)
            return null;
        if ($element == null)
            return $input;

        $input_size = SIZE($input);
        $new_size = $input_size + 1;
        $output = self::newArray($new_size);
        for ($n = 0; $n < $input_size; $n++)
            $output[$n] = $input[$n];
        $output[$input_size] = $element;
        return $output;
    }

    /**
     * Create array list from array of objects.
     * @param Object[] $input Array of objects.
     * @return ArrayList Resulting array list.
     */
    public static function createArrayList($input) {
		if ($input == null)
            return null;
        $output = new ArrayList();
        if (SIZE($input) == 0)
            return $output;
        foreach ($input as $obj)
            $output->add($obj);
        return $output;
    }

//if php
    /**
     * Create hash table from associative array.
     * @param Array $input Input array.
     * @return Hashtable Resulting hash table.
     */
    public static function createHashtable($input) {
		if ($input == null || !is_array($input))
            return null;
        $output = new Hashtable();
        if (SIZE($input) == 0)
            return $output;
        $keys = Arrays::getArrayKeys($input);
        while ($keys->moveNext()) {
            $key = /*(TString)*/$keys->current();
            if (is_string($key))
                $output->put($key, Arrays::getArrayValue($input, $key));
        }
        return $output;
    }

    /**
     * Get collection as associative array.
     * @return array
     */
    public static function toArray($input) {
        $result = self::newArray($input->count());
        $keys = $input->keys();
        while ($keys->moveNext()) {
            $key = /*(TString)*/$keys->current();
            $value = $input->get($key);
            if ($value instanceof TString) $value = $value->getValue();
            $result[$key] = $value;
        }
        return $result;
    }

    public static function getArrayKeys($input) {
        return new Enumerator(array_keys($input));
    }

    public static function getArrayValue($input, $key) {
        return $input[$key];
    }
}
