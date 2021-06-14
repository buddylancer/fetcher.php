<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller\Testing;

use Bula\Fetcher\Config;
use Bula\Fetcher\Context;
use Bula\Fetcher\Controller\Page;

use Bula\Objects\Helper;
use Bula\Objects\Request;
use Bula\Objects\Response;

require_once("Bula/Objects/Helper.php");
require_once("Bula/Objects/Request.php");
require_once("Bula/Objects/Response.php");
require_once("Bula/Fetcher/Controller/Page.php");

/**
 * Logic for getting test feed.
 */
class GetFeed extends Page
{

    /** Get test feed using parameters from request. */
    public function execute()
    {
        //$this->context->Request->initialize();
        $this->context->Request->extractAllVars();

        // Check source
        if (!$this->context->Request->contains("source")) {
            $this->context->Response->end("Source is required!");
            return;
        }
        $source = $this->context->Request->get("source");
        if (BLANK($source)) {
            $this->context->Response->end("Empty source!");
            return;
        }

        $this->context->Response->writeHeader("Content-type", "text/xml; charset=UTF-8");
        $this->context->Response->write(Helper::readAllText(CAT($this->context->LocalRoot->getValue(), "local/tests/input/U.S. News - ", $source, ".xml"))->getValue());
        $this->context->Response->end();
    }
}
