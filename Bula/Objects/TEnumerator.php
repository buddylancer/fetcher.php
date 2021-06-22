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
 * Very simple implementation of TEnumerator.
 */
class TEnumerator
{
    private $collection = null;
    private $pointer = -1;

    public function __construct ($elements)
    { $this->collection = $elements; }

    public function moveNext()
    {
        if ($this->pointer < SIZE($this->collection) - 1) {
           $this->current = $this->collection[++$this->pointer];
           return true;
        }
        return false;
    }

    public function getCurrent()
    {
        return $this->current;
    }
}
