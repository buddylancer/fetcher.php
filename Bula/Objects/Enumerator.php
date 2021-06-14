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
 * Very simple implementation of Enumerator.
 */
class Enumerator
{
    private $collection = null;
    private $pointer = -1;

    public function __construct ($elements)
    { $this->collection = $elements; }

    public function hasMoreElements()
    {
        return ($this->pointer < SIZE($this->collection) - 1);
    }

    public function nextElement()
    {
        return $this->current = ($this->pointer < SIZE($this->collection) - 1) ? $this->collection[++$this->pointer] : null;
    }

    public function moveNext()
    {
        return $this->nextElement() !== null;
    }

    public function current()
    {
        return $this->current;
    }
}
