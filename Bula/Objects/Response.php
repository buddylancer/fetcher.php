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
 * Helper class for processing server response.
 */
class Response
{

    /**
     * Write text to current response.
     * @param TString $input Text to write.
     */
    public static function write($input)
    {
        print CAT($input);
    }

    /**
     * Write header to current response.
     * @param TString $name Header name.
     * @param TString $value Header value.
     */
    public static function writeHeader($name, $value)
    {
        header(CAT($name, ": ", $value));
    }

    /**
     * End current response.
     * @param TString $input Text to write before ending response.
     */
    public static function end($input)
    {
        self::write($input);
        die();
    }
}

