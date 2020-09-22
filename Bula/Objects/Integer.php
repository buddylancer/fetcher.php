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

require_once("TString.php");

class Integer
{
    private $value = 0;

    public function __construct($input)
    {
        self::set($input);
    }

    public function set($input)
    {
        $this->value = intval($input instanceof TString ? $input->getValue() : $input);
    }

    public function get()
    {
        return $this->value;
    }

    public function toString()
    {
        return CAT($this->value);
    }

}

