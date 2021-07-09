<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula;

use Bula\Objects\TArrayList;
use Bula\Objects\THashtable;
use Bula\Objects\TString;
use Bula\Objects\Strings;

require_once("Bula/Objects/TArrayList.php");
require_once("Bula/Objects/THashtable.php");
require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/Strings.php");

class Internal
{
    /**
     * Remove HTML tags from string except allowed ones.
     * @param TString $input Input string.
     * @param TString $except List of allowed tags (do not remove).
     * @return TString Resulting string.
     */
    public static function removeTags($input, $except= null )
    {
        if ($except != null && $except instanceof TString) $except = $except->getValue();
        return new TString(strip_tags($input->getValue(), $except == null ? null : $except));
    }

    /**
     * Call static method of given class using provided arguments.
     * @param TString $className Class name.
     * @param TString $methodName Method name.
     * @param TArrayList $args List of arguments.
     * @return Object Result of method execution.
     */
    public static function callStaticMethod($className, $methodName, $args = null)
    {
        $className = "\\" . str_replace("/", "\\", $className);
        if ($args != null && $args->size() > 0) {
            //return $className::$methodName($args->toArray());
            $reflectionMethod = new \ReflectionMethod($className, $methodName);
            return $reflectionMethod->invokeArgs(null, $args->toArray());
        }
        else
            return $className::$methodName();
    }

    /**
     * Call method of given class using provided arguments.
     * @param TString $className Class name.
     * @param TString $methodName Method name.
     * @param TArrayList $args List of arguments.
     * @return Object Result of method execution.
     */
    public static function callMethod($className, $conArgs, $methodName, $exeArgs = null)
    {
        require_once(CAT($className, ".php"));
        $className = CAT("\\", Strings::replace("/", "\\", $className));
        $class = new \ReflectionClass($className);
        $instance = $class->newInstanceArgs($conArgs->toArray());
        $reflectionMethod = new \ReflectionMethod($className, $methodName);
        $result = null;
        if ($exeArgs != null)
            $result = $reflectionMethod->invokeArgs($instance, $exeArgs->toArray());
        else
            $result = $reflectionMethod->invoke($instance);
        return $result;
        //if ($args != null && $args->count() > 0)
        //    return $className::$methodName($args->toArray());
        //else
        //    return $className::$methodName();
    }

    private static $allowedChars = "€₹₽₴—•–‘’—№…"; //TODO!!! Hardcode Russian Ruble, Ukranian Hryvnia etc for now

    public static function cleanChars($input)
    {
        if ($input instanceof TString) $input = $input->getValue();
        $len = mb_strlen($input);
        $output = "";
        for ($n = 0; $n < $len; $n++) {
            $char1 = mb_substr($input, $n, 1);
            $len1 = strlen($char1);
            if ($len1 < 3 || mb_strpos(self::$allowedChars, $char1) !== false)
                $output = CAT($output, $char1);
        }
        return new TString(trim($output));
    }

    public static function codeToUtf($dec)
    {
        if ($dec < 128) { // 2 ^ 7
            $utf = chr($dec);
        }
        else if ($dec < 2048) {
            $utf = chr(192 + (($dec - ($dec % 64)) / 64));
            $utf .= chr(128 + ($dec % 64)); // 2 ^ 6
        }
        else {
            $utf = chr(224 + (($dec - ($dec % 4096)) / 4096)); // 2 ^ 12
            $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
            $utf .= chr(128 + ($dec % 64));
        }
        return $utf;
    }

    /**
     * Stub for calling fetch_rss().
     * @param TString $url RSS-feed url.
     * @return Object[] Array of fetched items.
     */
    public static function fetchRss($url)
    {
        return fetch_rss($url);
    }
}
