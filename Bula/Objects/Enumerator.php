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

/**
 * Very simple implementation of Java Enumerator.
 */
class Enumerator
{
    private $collection = null;
    private $pointer = -1;

    public function __construct($elements)
    {
        $this->collection = $elements;
        $this->pointer = -1;
    }

    public function moveNext()
    {
        if ($this->pointer < SIZE($this->collection) - 1) {
            $this->pointer++;
            return true;
        }
        return false;
    }

    public function hasMoreElements()
    {
        return ($this->pointer < SIZE($this->collection) - 1);
    }

    public function current()
    {
        if ($this->pointer >= 0)
            return $this->collection[$this->pointer];
        return null;
    }

    public function nextElement()
    {
        moveNext();
        return current();
    }
}
