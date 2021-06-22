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
use Bula\Objects\TEnumerator;
use Bula\Objects\THashtable;

require_once("Arrays.php");
require_once("TEnumerator.php");
require_once("THashtable.php");

/**
 * Base helper class for processing query/form request.
 */
class TRequestBase
{
    /** Current response */
    public $response = null;

    public function __construct($currentTRequest= null)
    {
        if (NUL($currentTRequest))
            return;
    }

    /**
     * Get all variables of given type.
     * @param Integer $type Required type.
     * @return THashtable TRequested variables.
     */
    public function getVars($type)
    {
        $output = THashtable::create();
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
     * @return TString TRequested variable.
     */
    public function getVar($type, $name)
    {
        $var = filter_input($type, $name);
        return $var == null ? null : new TString($var);
    }
}

