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

require_once("TArrayListBase.php");

/**
 * Straight-forward implementation of ArrayList.
 */
class TArrayList extends TArrayListBase
{

    /** Create new array list. */
    public static function create()
    {
        return new TArrayList();
    }

    /**
     * Add multiple objects.
     * @param Object[] $inputs Array of objects.
     * @return Integer Number of added objects,
     */
    public function addAll($inputs)
    {
        $counter = 0;
        foreach ($inputs as $input) {
            $this->add($input);
            $counter++;
        }
        return $counter;
    }

    /**
     * Create array list from array of objects.
     * @param Object[] $input Array of objects.
     * @return TArrayList Resulting array list.
     */
    public static function createFrom($input)
    {
        if ($input == null)
            return null;
        $output = self::create();
        if (SIZE($input) == 0)
            return $output;
        foreach ($input as $obj)
            $output->add($obj);
        return $output;
    }

    /**
     * Merge array lists.
     * @param TArrayList $input Original array list.
     * @param TArrayList $extra Array list to merge with original one.
     * @return TArrayList Resulting array list.
     */
    public function merge($extra)
    {
        $output = self::create();
        for ($n1 = 0; $n1 < $this->size(); $n1++)
            $output->add($this->get($n1));
        if ($extra == null)
            return $output;
        for ($n2 = 0; $n2 < $extra->size(); $n2++)
            $output->add($extra->get($n2));
        return $output;
    }

}
