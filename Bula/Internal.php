<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula;

use Bula\Objects\ArrayList;

require_once("Bula/Objects/ArrayList.php");

class Internal
{
    /**
     * Call static method of given class using provided arguments.
     * @param TString $className Class name.
     * @param TString $methodName Method name.
     * @param ArrayList $args List of arguments.
     * @return Object Result of method execution.
     */
    public static function callStaticMethod($className, $methodName, $args = null)
    {
        $className = "\\" . str_replace("/", "\\", $className);
        if ($args != null && $args->count() > 0) {
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
     * @param ArrayList $args List of arguments.
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
