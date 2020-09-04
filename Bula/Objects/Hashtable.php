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

use Bula\Objects\Enumerator;

require_once("ArrayList.php");
require_once("Collection.php");
require_once("Enumerator.php");
require_once("TString.php");

/**
 * Straight-forward implementation of Java Hashtable object.
 */
class Hashtable extends Collection {
	public function __construct() {
	}

    /**
     * Check whether collection contains an object.
     * @param Object $input Object to check.
     * @return Boolean
     */
    public function contains($input) {
        return in_array($input, $this->collection);
    }

    /**
     * Check whether collection contains a key.
     * @param Object $key Key to check.
     * @return Boolean
     */
    public function containsKey($key) {
        return array_key_exists(CAT($key), $this->collection);
    }

    public function copyTo(&$array, $index) {
        //TODO
    }

    public function cloneMe() {
        $output = $this;
        return $output;
    }

    /**
     * Get an object by a key.
     * @param Object $key Key of an object.
     * @return Object Resulting object or null if key not exists.
     */
    public function get($key) {
        $key_var = $this->checkKey($key);
        if (!$this->containsKey($key_var))
            return null;
        if ($this->pull_values)
            return $this->collection[$key_var];
        else
            return $this->pullObject($this->collection[$key_var]);
    }

    /**
     * Check whether collection is empty.
     * @return Boolean
     */
    public function isEmpty() {
        return SIZE($this->collection) == 0;
    }

    /**
     * Get collection's keys.
     * @return Enumerator Resulting Enumerator object
     */
    public function keys() {
        return new Enumerator(array_keys($this->collection));
	}

    /**
     * Put an object into collection.
     * @param Object $key Key to assign.
     * @param Object $input Object to add.
     */
    public function put($key, $input) {
        $key_var = $this->checkKey($key);
        $this->collection[$key_var] = $this->pushValue($input);
    }

    /**
     * Remove an object by a key.
     * @param Object $key Key of an object.
     * @return Boolean True - an object removed, False - a key not found.
     */
    public function remove($key) {
        if (!$this->containsKey($key))
            return false;
        $key_var = $this->checkKey($key);
        $this->collection[$key_var] = null;
        unset($this->collection[$key_var]);
        return true;
    }

    /**
     * Get size of collection.
     * @return Integer
     */
    public function count() {
        return sizeof($this->collection);
    }

	/**
     * Get an array of values from collection.
     * @return Object[]
     */
    public function values() {
        return new Enumerator(array_values($this->collection));
	}

    // Return string value of a key.
    protected function checkKey($key) {
		if ($key == null)
			return null;
		$key_var = $key instanceof TString ? $key->getValue() : $key;
		if ($key_var == "")
			return null;
		return $key_var;
	}

}
