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
class Action extends Page {
    private static $actions_array = null;

    private static function initialize() {
        self::$actions_array = array(
        //action name            page                   post      code
        "do_redirect_item",     "DoRedirectItem",       0,        0,
        "do_redirect_source",   "DoRedirectSource",     0,        0,
        "do_clean_cache",       "DoCleanCache",         0,        1,
        "do_test_items",        "DoTestItems",          0,        1
        );
    }

    /** Execute main logic for required action. */
    public function execute() {
        if (self::$actions_array == null)
            self::initialize();

        $action_info = Request::testPage(self::$actions_array);

        // Test action name
        if (!$action_info->containsKey("page"))
            Response::end("Error in parameters -- no page");

        // Test action context
        if (INT($action_info->get("post_required")) == 1 && INT($action_info->get("from_post")) == 0)
            Response::end("Error in parameters -- inconsistent pars");

        Request::initialize();
        if (INT($action_info->get("post_required")) == 1)
            Request::extractPostVars();
        else
            Request::extractAllVars();

        //TODO!!!
        //if (!Request::CheckReferer(Config::$Site))
        //    err404();

        if (INT($action_info->get("code_required")) == 1) {
            if (!Request::contains("code") || !EQ(Request::get("code"), Config::SECURITY_CODE)) //TODO -- hardcoded!!!
                Response::end("No access.");
        }

        $action_class = CAT("Bula/Fetcher/Controller/Actions/", $action_info->get("class"));
        //Config::includeFile(CAT($action_class, ".php"));
        //Internal::callStaticMethod($action_class, "execute");
        Internal::callMethod($action_class, new ArrayList($this->context), "execute", null);

        if (DBConfig::$Connection != null) {
            DBConfig::$Connection->close();
            DBConfig::$Connection = null;
        }
    }
}
