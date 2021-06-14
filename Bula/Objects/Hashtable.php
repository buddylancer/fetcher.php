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

use Bula\Objects\Enumerator;

require_once("ArrayList.php");
require_once("Collection.php");
require_once("Enumerator.php");
require_once("TString.php");

/**
 * Straight-forward implementation of Java Hashtable object.
 */
class Hashtable extends Collection
{
    public function __construct()
    {
    }

    /**
     * Check whether collection contains an object.
     * @param Object $input Object to check.
     * @return Boolean
     */
    public function contains($input)
    {
        return in_array($input, $this->collection);
    }

    /**
     * Check whether collection contains a key.
     * @param Object $key Key to check.
     * @return Boolean
     */
    public function containsKey($key)
    {
        return array_key_exists(CAT($key), $this->collection);
    }

    public function copyTo(&$array, $index)
    {
        //TODO
    }

    public function cloneMe()
    {
        $output = $this;
        return $output;
    }

    /**
     * Get an object by a key.
     * @param Object $key Key of an object.
     * @return Object Resulting object or null if key not exists.
     */
    public function get($key)
    {
        $keyVar = $this->checkKey($key);
        if (!$this->containsKey($keyVar))
            return null;
        if ($this->pullValues)
            return $this->collection[$keyVar];
        else
            return $this->pullObject($this->collection[$keyVar]);
    }

    /**
     * Check whether collection is empty.
     * @return Boolean
     */
    public function isEmpty()
    {
        return SIZE($this->collection) == 0;
    }

    /**
     * Get collection's keys.
     * @return Enumerator Resulting Enumerator object
     */
    public function keys()
    {
        return new Enumerator(array_keys($this->collection));
    }

    /**
     * Put an object into collection.
     * @param Object $key Key to assign.
     * @param Object $input Object to add.
     */
    public function put($key, $input)
    {
        $keyVar = $this->checkKey($key);
        $this->collection[$keyVar] = $this->pushValue($input);
    }

    /**
     * Remove an object by a key.
     * @param Object $key Key of an object.
     * @return Boolean True - an object removed, False - a key not found.
     */
    public function remove($key)
    {
        if (!$this->containsKey($key))
            return false;
        $keyVar = $this->checkKey($key);
        $this->collection[$keyVar] = null;
        unset($this->collection[$keyVar]);
        return true;
    }

    /**
     * Get size of collection.
     * @return Integer
     */
    public function size()
    {
        return sizeof($this->collection);
    }

    /**
     * Get an array of values from collection.
     * @return Object[]
     */
    public function values()
    {
        return new Enumerator(array_values($this->collection));
    }

    // Return string value of a key.
    protected function checkKey($key)
    {
        if ($key == null)
            return null;
        $keyVar = $key instanceof TString ? $key->getValue() : $key;
        if ($keyVar == "")
            return null;
        return $keyVar;
    }

}
