<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller\Testing;

use Bula\Objects\Helper;
use Bula\Objects\Request;
use Bula\Objects\Response;
use Bula\Fetcher\Controller\Page;

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
        Request::initialize();
        Request::extractAllVars();

         // Check source
        if (!Request::contains("source"))
            Response::end("Source is required!");
        $source = Request::get("source");
        if (BLANK($source))
            Response::end("Empty source!");

        Response::writeHeader("Content-type", "text/xml; charset=UTF-8");
        Response::write(Helper::readAllText(CAT($this->context->LocalRoot->getValue(), "local/tests/input/U.S. News - ", $source, ".xml"))->getValue());
    }
}
