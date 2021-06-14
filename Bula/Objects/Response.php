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
    /** Current response */

    public function __construct($response)
    {
    }

    /**
     * Write text to current response.
     * @param TString $input Text to write.
     */
    public function write($input)
    {
        print CAT($input);
    }

    /**
     * Write header to current response.
     * @param TString $name Header name.
     * @param TString $value Header value.
     */
    public function writeHeader($name, $value)
    {
        header(CAT($name, ": ", $value));
    }

    /**
     * End current response.
     * @param TString $input Text to write before ending response.
     */
    public function end($input= null)
    {
        if (!NUL($input))
            self::write($input);
        die();
    }
}

