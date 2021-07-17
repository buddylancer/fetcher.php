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
use Bula\Objects\TArrayList;
use Bula\Objects\Helper;
use Bula\Objects\Regex;

require_once("TEnumerator.php");
require_once("TArrayList.php");
require_once("Helper.php");
require_once("Regex.php");

/**
 * Helper class for manipulation with text translations.
 */
class Translator
{
    private static $pairs = null;

    /**
     * Initialize translation table.
     * @param TString @fileName Filename to load translation table from.
     * @return Integer Number of actual pairs in translation table.
     */
    public static function initialize($fileName)
    {
        $lines = Helper::readAllLines($fileName);
        if ($lines == null)
            return 0;
        self::$pairs = new TArrayList($lines);
        return self::$pairs->size();
    }

    /**
     * Translate content.
     * @param TString $input Input content to translate.
     * @return TString Translated content.
     */
    public static function translate($input)
    {
        $output = $input;
        for ($n = 0; $n < self::$pairs->size(); $n++) {
            $line = Strings::trim(self::$pairs->get($n), "\r\n");
            if (BLANK($line) || $line->indexOf("#") == 0)
                continue;
            if ($line->indexOf("|") == -1)
                continue;

            $chunks = null;
            $needRegex = false;
            if ($line->indexOf("/") == 0) {
                $chunks = Strings::split("\\|", $line->substring(1));
                $needRegex = true;
            }
            else {
                $chunks = Strings::split("\\|", $line);
            }
            $to = SIZE($chunks) > 1 ? $chunks[1] : "";
            $output = $needRegex ?
                Regex::replace($output, $chunks[0], $to) :
                Strings::replace($chunks[0], $to, $output);
        }
        return $output;
    }

    /**
     * Check whether translation table is initialized (loaded).
     * @return Boolean True if the table is initialized, False otherwise.
     */
    public static function isInitialized()
    {
        return self::$pairs != null;
    }
}
