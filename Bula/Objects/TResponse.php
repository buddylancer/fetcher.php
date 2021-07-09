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

use Bula\Objects\Translator;

require_once("Bula/Objects/Translator.php");

/**
 * Helper class for processing server response.
 */
class TResponse
{
    /** Current response */

    /**
     * Default constructor.
     * @param Object $currentResponse Current http response object.
     */
    public function __construct($currentResponse)
    {
    }

    /**
     * Write text to current response.
     * @param TString $input Text to write.
     * @param TString $lang Language to tranlsate to (default - none).
     */
    public function write($input, $langFile= null)
    {
        if ($langFile != null) {
            if (!Translator::isInitialized())
                Translator::initialize($langFile);
            if (Translator::isInitialized())
                $input = Translator::translate($input);
        }
        print CAT($input);
    }

    /**
     * Write header to current response.
     * @param TString $name Header name.
     * @param TString $value Header value.
     * @param TString $encoding Response encoding.
     */
    public function writeHeader($name, $value, $encoding= null)
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

