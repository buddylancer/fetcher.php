<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller;

use Bula\Objects\Hashtable;

use Bula\Fetcher\Config;
use Bula\Objects\TString;
use Bula\Objects\DateTimes;

require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/Hashtable.php");

/**
 * Basic logic for generating Page block.
 */
abstract class Page {
    protected $context = null;

    public function __construct($context) {
        $this->context = $context;
        //echo "In Page constructor -- " . print_r($context, true);
    }

    /** Execute main logic for page block */
    abstract public function execute();

    /**
     * Merge template with variables and write to engine.
     * @param TString $template Template name.
     * @param Hashtable $prepare Prepared variables.
     */
    public function write($template, $prepare) {
        $engine = $this->context->getEngine();
        $engine->write($engine->showTemplate($template, $prepare));
    }
}
