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
 * Straight-forward implementation of Array.
 */
class TArray
{
    private $content;

    /** Default constructor. */
    public function __construct($size)
    {
        $this->instantiate($size);
    }

    private function instantiate($size)
    {
        $content = array[$size];
    }

    public function size()
    {
        return sizeof($content);
    }

    public function set($pos, $value)
    {
        if ($pos >= $this->size())
            return false;
        $content[$pos] = $value;
        return true;
    }

    public function get($pos)
    {
        if ($pos >= $this->size())
            return false;
        return $content[$pos];
    }

    public function add($value)
    {
        $cloned = $this->clone();
        $this->instantiate($this->size() + 1);
        for ($n = 0; $n < $cloned->size(); $n++)
            $this->set($n, $cloned->get($n));
        $this->set($cloned->size() + 1, $value);
    }

    public function clone()
    {
        $cloned = new TArray($this->size());
        for ($n = 0; $n < $this->size(); $n++)
            $cloned->set($n, $this->get($n));
        return $cloned;
    }

    public function toArray()
    {
        return $content;
    }
}
