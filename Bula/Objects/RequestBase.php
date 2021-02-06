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

use Bula\Objects\Arrays;
use Bula\Objects\Enumerator;
use Bula\Objects\Hashtable;

require_once("Arrays.php");
require_once("Enumerator.php");
require_once("Hashtable.php");

/**
 * Base helper class for processing query/form request.
 */
class RequestBase
{

    /**
     * Get all variables of given type.
     * @param Integer $type Required type.
     * @return Hashtable Requested variables.
     */

    public static function getVars($type)
    {
        $output = Arrays::newHashtable();
        $vars = filter_input_array($type);
        if ($vars === false || $vars == null)
            return $output;
        foreach ($vars as $key => $value)
            $output->put($key, $value == null ? "" : $value);
        return $output;
    }

    /**
     * Get a single variable of given type.
     * @param Integer $type Required type.
     * @param TString $name Variable name.
     * @return TString Requested variable.
     */

    public static function getVar($type, $name)
    {
        $var = filter_input($type, $name);
        return $var == null ? null : new TString($var);
    }

}

