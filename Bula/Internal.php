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

class Internal {
    /**
     * Call static method of given class using provided arguments.
     * @param TString $class_name Class name.
     * @param TString $method_name Method name.
     * @param ArrayList $args List of arguments.
     * @return Object Result of method execution.
     */
    public static function callStaticMethod($class_name, $method_name, $args = null) {
        $class_name = "\\" . str_replace("/", "\\", $class_name);
        if ($args != null && $args->count() > 0) {
            //return $class_name::$method_name($args->toArray());
            $reflectionMethod = new \ReflectionMethod($class_name, $method_name);
            return $reflectionMethod->invokeArgs(null, $args->toArray());
        }
        else
            return $class_name::$method_name();
    }

    /**
     * Call method of given class using provided arguments.
     * @param TString $class_name Class name.
     * @param TString $method_name Method name.
     * @param ArrayList $args List of arguments.
     * @return Object Result of method execution.
     */
    public static function callMethod($class_name, $con_args, $method_name, $exe_args = null) {
        require_once($class_name . ".php");
        $class_name = "\\" . str_replace("/", "\\", $class_name);
        $class = new \ReflectionClass($class_name);
        $instance = $class->newInstanceArgs($con_args->toArray());
        $reflectionMethod = new \ReflectionMethod($class_name, $method_name);
        $result = null;
        if ($exe_args != null)
            $result = $reflectionMethod->invokeArgs($instance, $exe_args->toArray());
        else
            $result = $reflectionMethod->invoke($instance);
        return $result;
        //if ($args != null && $args->count() > 0)
        //    return $class_name::$method_name($args->toArray());
        //else
        //    return $class_name::$method_name();
    }

    /**
     * Stub for calling fetch_rss().
     * @param TString $url RSS-feed url.
     * @return Object[] Array of fetched items.
     */
    public static function fetchRss($url) {
        return fetch_rss($url);
    }
}
