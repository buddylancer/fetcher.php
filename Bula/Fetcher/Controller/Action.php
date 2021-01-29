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

use Bula\Internal;
use Bula\Fetcher\Config;
use Bula\Objects\Request;
use Bula\Objects\Response;
use Bula\Objects\ArrayList;
use Bula\Objects\Hashtable;
use Bula\Model\DBConfig;

require_once("Bula/Meta.php");
require_once("Bula/Internal.php");
require_once("Bula/Model/DBConfig.php");
require_once("Bula/Objects/ArrayList.php");
require_once("Bula/Objects/Hashtable.php");
require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/Request.php");
require_once("Bula/Objects/Response.php");
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

        $actionInfo = Request::testPage(self::$actionsArray);

        // Test action name
        if (!$actionInfo->containsKey("page")) {
            Response::end("Error in parameters -- no page");
            return;
        }

        // Test action context
        if (INT($actionInfo->get("post_required")) == 1 && INT($actionInfo->get("from_post")) == 0) {
            Response::end("Error in parameters -- inconsistent pars");
            return;
        }

        Request::initialize();
        if (INT($actionInfo->get("post_required")) == 1)
            Request::extractPostVars();
        else
            Request::extractAllVars();

        //TODO!!!
        //if (!Request::CheckReferer(Config::$Site))
        //    err404();

        if (INT($actionInfo->get("code_required")) == 1) {
            if (!Request::contains("code") || !EQ(Request::get("code"), Config::SECURITY_CODE)) { //TODO -- hardcoded!!!
                Response::end("No access.");
                return;
            }
        }

        $actionClass = CAT("Bula/Fetcher/Controller/Actions/", $actionInfo->get("class"));
        //Config::includeFile(CAT($actionClass, ".php"));
        $args0 = new ArrayList(); $args0->add($this->context);
        Internal::callMethod($actionClass, $args0, "execute", null);

        if (DBConfig::$Connection != null) {
            DBConfig::$Connection->close();
            DBConfig::$Connection = null;
        }
    }
}
