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
use Bula\Objects\Strings;
use Bula\Objects\TRequest;
use Bula\Objects\TResponse;
use Bula\Objects\TString;

require_once("Bula/Objects/Helper.php");
require_once("Bula/Objects/Strings.php");
require_once("Bula/Objects/TRequest.php");
require_once("Bula/Objects/TResponse.php");
require_once("Bula/Objects/TString.php");
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
        $encoding = new TString("UTF-8");
        if ($this->context->Request->contains("encoding"))
            $encoding = $this->context->Request->get("encoding");

        $from = new TString("tests/input");
        if ($this->context->Request->contains("from"))
            $from = $this->context->Request->get("from");

        $this->context->Response->writeHeader("Content-type", CAT("text/xml; charset=", $encoding), $encoding);
        $filename = Strings::concat($this->context->LocalRoot, "local/", $from, "/", $source, ".xml");
        if ($filename->indexOf("..") == -1) {
            $content = Helper::readAllText($filename, $encoding);
            if (!BLANK($content))
                $this->context->Response->write($content);
        }
        $this->context->Response->end();
    }
}
