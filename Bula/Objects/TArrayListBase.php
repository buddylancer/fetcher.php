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

require_once("TCollection.php");

/**
 * Straight-forward implementation of TArrayList.
 */
class TArrayListBase extends TCollection
{
    /** Constructor for array list with variable number of objects */
    public function __construct(/*...*/)
    {
        $this->collection[] = "_A"; // Zero element marks that this is TArrayList, not THashtable
        $args = func_get_args();
        if (SIZE($args) > 0)
            $this->pullValues = true;
        foreach ($args as $arg) {
            if (is_array($arg))
                $this->addAll($arg);
            else
                $this->add($arg);
        }
    }

    /**
     * Add object to collection.
     * @param Object $input Object to add.
     */
    private function addObject($input)
    {
        $this->collection[] = $input;
    }

    /**
     * Add object.
     * @param Object $input Object to add.
     */
    public function add($input)
    {
        $this->addObject($this->pushValue($input));
    }

    /**
     * Check whether the list contains an object.
     * @param Object $input Object to check.
     * @return Boolean
     */
    public function contains($input)
    {
        return in_array($input, $this->collection);
    }

    /**
     * Get an object.
     * @param Integer $n Index of an object.
     * @return Object Object or null (if index not exists)
     */
    public function get($n)
    {
        if (isset($this->collection[$n+1])) {
            if ($this->pullValues)
                return $this->collection[$n+1];
            else
                return $this->pullObject($this->collection[$n+1]);
        }
        return null;
    }

    /**
     * Identify the index of an object.
     * @param Object $input Object to check.
     * @return Integer Index or -1 (if object not exists).
     */
    public function indexOf($input)
    {
        for ($n = 1; $n < SIZE($this->collection); $n++) {
            if ($input === $this->collection[$n])
                return $n - 1;
        }
        return -1;
    }

    /**
     * Check whether the list is empty.
     * @return Boolean
     */
    public function isEmpty()
    {
        return SIZE($this->collection) == 1;
    }

    /**
     * Identify the last index of an object.
     * @param Object $input Object to check.
     * @return Integer Last index or -1 (if object not exists).
     */
    public function lastIndexOf($input)
    {
        for ($n = SIZE($this->collection) - 1; $n >= 0; $n--) {
            if ($input == $this->collection[$n+1])
                return $n;
        }
        return -1;
    }

    /**
     * Remove an object.
     * @param Object $input Object to remove.
     * @return Boolean True if removed, False if object not exists.
     */
    public function remove($input)
    {
        if (is_integer($input))
            return removeByIndex($input);
        $index = $this->indexOf($input);
        if ($index != -1) {
            unset($this->collection[$index+1]);
            return true;
        }
        return false;
    }

    /**
     * Remove an object (by index)
     * @param Object $index Index of an object.
     * @return Boolean True if removed, False if index not exists.
     */
    public function removeByIndex($index)
    {
        if (isset($this->collection[$index+1])) {
            unset($this->collection[$index+1]);
            return true;
        }
        return false;
    }

    /**
     * Set an object with given index.
     * @param Integer $index Index of an object to replace (or set).
     * @param Object $input Object to replace (or set).
     */
    public function set($index, $input)
    {
        $this->collection[$index+1] = $input;
    }

    /**
     * Get size of the list.
     * @return Integer
     */
    public function size()
    {
        return SIZE($this->collection) - 1;
    }

    /**
     * Get collection as array.
     * @return Object[]
     */
    public function toArray($from = 1)
    {
        return array_slice($this->collection, $from);
    }
}
