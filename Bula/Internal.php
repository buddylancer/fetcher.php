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

require_once("Bula/Objects/TArrayList.php");
require_once("Bula/Objects/THashtable.php");
require_once("Bula/Objects/TString.php");

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
        require_once($className . ".php");
        $className = "\\" . str_replace("/", "\\", $className);
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
