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

use Bula\Objects\Arrays;
use Bula\Objects\TArrayList;
use Bula\Objects\TString;

require_once("Arrays.php");
require_once("TArrayList.php");
require_once("TString.php");

/**
 * Base class for TArrayList and THashtable.
 */
abstract class TCollection
{
    protected $collection = array();
    protected $pullValues = false;

    /**
     * Set mode for pulling objects from collection.
     * @param Boolean $input True - pull as values, False - pull as objects.
     */
    public function setPullValues($input)
    {
        $this->pullValues = $input;
    }
    public function getPullValues()
    {
        return $this->pullValues;
    }

    /**
     * Convert (push) object to value.
     * @param Object $input Input object.
     * @return Object Resulting value.
     */
    protected function pushValue($input)
    {
        if ($input instanceof TArrayList)
            return $input->toArray(0);
        else if ($input instanceof THashtable)
            return $input->toArray();
        else if ($input instanceof TString)
            return $input->getValue();
        return $input;
    }

    /**
     * Convert (pull) value to object.
     * @param Object $input Input value.
     * @return Object Resulting object.
     */
    protected function pullObject($input)
    {
        if (is_array($input)) {
            if (isset($input[0]) && $input[0] == "_A") {
                $result = new TArrayList();
                for ($n = 1; $n < sizeof($input); $n++)
                    $result->add($input[$n]);
                return $result;
            }
            else
                return Arrays::createTHashtable($input);
        }
        else if (is_string($input))
            return new TString($input);
        return $input;
    }

    //abstract function isEmpty();

    abstract function size();

    //abstract function toArray();

    public function toString()
    {
        return "Not implemented!";
    }

    public function toArray()
    {
        return $this->collection;
    }
}
