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

use Bula\Objects\TEnumerator;

require_once("TArrayList.php");
require_once("TCollection.php");
require_once("TEnumerator.php");
require_once("THashtableBase.php");
require_once("TString.php");

/**
 * Straight-forward implementation of Java THashtable object.
 */
class THashtable extends THashtableBase
{
    public function __construct()
    {
    }

    /**
     * Create new hash table.
     * @return THashtable New hash table.
     */
    public static function create()
    {
        return new THashtable();
    }

    /**
     * Merge hash tables.
     * @param THashtable $extra Hash table to merge with original one.
     * @return THashtable Merged hash table.
     */
    public function merge($extra)
    {
        if ($extra == null)
            return $this;

        $output = self::create();

        $keys1 = new TEnumerator($this->keys());
        while ($keys1->moveNext()) {
            $key1 = $keys1->getCurrent();
            $output->put($key1, $this->get($key1));
        }

        $keys2 = new TEnumerator($extra->keys());
        while ($keys2->moveNext()) {
            $key2 = $keys2->getCurrent();
            $output->put($key2, $extra->get($key2));
        }
        return $output;
    }

}
