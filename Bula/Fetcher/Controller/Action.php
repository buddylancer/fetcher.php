<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher\Controller;

use Bula\Internal;
use Bula\Fetcher\Config;
use Bula\Fetcher\Context;
use Bula\Objects\TRequest;
use Bula\Objects\TResponse;
use Bula\Objects\TArrayList;
use Bula\Objects\THashtable;
use Bula\Model\DBConfig;

require_once("Bula/Meta.php");
require_once("Bula/Internal.php");
require_once("Bula/Model/DBConfig.php");
require_once("Bula/Objects/TArrayList.php");
require_once("Bula/Objects/THashtable.php");
require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/TRequest.php");
require_once("Bula/Objects/TResponse.php");
require_once("Bula/Fetcher/Controller/Util.php");
require_once("Bula/Fetcher/Controller/Page.php");

/**
 * Logic for executing actions.
 */
class Action extends Page
{
    private static $actionsArray = null;

    private static function initialize()
    {
        self::$actionsArray = array(
        //action name            page                   post      code
        "do_redirect_item",     "DoRedirectItem",       0,        0,
        "do_redirect_source",   "DoRedirectSource",     0,        0,
        "do_clean_cache",       "DoCleanCache",         0,        1,
        "do_test_items",        "DoTestItems",          0,        1
        );
    }

    /** Execute main logic for required action. */
    public function execute()
    {
        if (self::$actionsArray == null)
            self::initialize();

        $actionInfo = $this->context->Request->testPage(self::$actionsArray);

        // Test action name
        if (!$actionInfo->containsKey("page")) {
            $this->context->Response->end("Error in parameters -- no page");
            return;
        }

        // Test action context
        if (INT($actionInfo->get("post_required")) == 1 && INT($actionInfo->get("from_post")) == 0) {
            $this->context->Response->end("Error in parameters -- inconsistent pars");
            return;
        }

        //$this->context->Request->initialize();
        if (INT($actionInfo->get("post_required")) == 1)
            $this->context->Request->extractPostVars();
        else
            $this->context->Request->extractAllVars();

        //TODO!!!
        //if (!$this->context->Request->CheckReferer(Config::$Site))
        //    err404();

        if (INT($actionInfo->get("code_required")) == 1) {
            if (!$this->context->Request->contains("code") || !EQ($this->context->Request->get("code"), Config::SECURITY_CODE)) { //TODO -- hardcoded!!!
                $this->context->Response->end("No access.");
                return;
            }
        }

        $actionClass = CAT("Bula/Fetcher/Controller/Actions/", $actionInfo->get("class"));
        //Config::includeFile(CAT($actionClass, ".php"));
        $args0 = new TArrayList(); $args0->add($this->context);
        Internal::callMethod($actionClass, $args0, "execute", null);
    }
}
