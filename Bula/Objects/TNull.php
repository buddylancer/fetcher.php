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
 * Implementation of DB NULL object.
 */
class TNull
{
    private static $value;

    private function __construct()
    {
        $value = null;
    }

    /**
     * Get NULL value.
     * @return TNull NULL value.
     */
    public static function getValue()
    {
        if (self::$value == null)
            self::$value = new TNull();
        return self::$value;
    }
}
