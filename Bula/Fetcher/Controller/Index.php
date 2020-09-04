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

use Bula\Fetcher\Config;
use Bula\Fetcher\Context;
use Bula\Objects\ArrayList;
use Bula\Objects\Hashtable;
use Bula\Objects\Regex;
use Bula\Objects\RegexOptions;
use Bula\Objects\Request;
use Bula\Objects\Response;
use Bula\Model\DBConfig;
use Bula\Model\DataAccess;
use Bula\Fetcher\Controller\Util;
use Bula\Fetcher\Controller\Engine;

require_once("Bula/Meta.php");
require_once("Bula/Model/DBConfig.php");
require_once("Bula/Objects/ArrayList.php");
require_once("Bula/Objects/Hashtable.php");
require_once("Bula/Objects/Regex.php");
require_once("Bula/Objects/RegexOptions.php");
require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/Request.php");
require_once("Bula/Objects/Response.php");
require_once("Bula/Model/DOBase.php");
require_once("Bula/Fetcher/Controller/Page.php");
require_once("Bula/Fetcher/Controller/Engine.php");
require_once("Bula/Fetcher/Controller/Util.php");

/**
 * Controller for main Index page.
 */
class Index extends Page {
    private static $pages_array = null;

    private static function initialize() {
        self::$pages_array = array(
            // page name,   class,          post,   code
            "home",         "Home",         0,      0,
            "items",        "Items",        0,      0,
            "view_item",    "ViewItem",     0,      0,
            "sources",      "Sources",      0,      0
        );
    }

    public function execute() {
        if (self::$pages_array == null)
            self::initialize();

        DataAccess::setErrorDelegate("Bula\Objects\Response::end");

        $page_info = Request::testPage(self::$pages_array, "home");

        // Test action name
        if (!$page_info->containsKey("page"))
            Response::end("Error in parameters -- no page");

        $page_name = /*(TString)*/$page_info->get("page");
        $class_name = /*(TString)*/$page_info->get("class");

        Request::initialize();
        if (INT($page_info->get("post_required")) == 1)
            Request::extractPostVars();
        else
            Request::extractAllVars();
        //echo "In Index -- " . print_r($this, true);
        $this->context->set("Page", $page_name);

        $engine = $this->context->pushEngine(true);

        $Prepare = new Hashtable();
        $Prepare->put("[#Site_Name]", Config::SITE_NAME);
        $p_from_vars = Request::contains("p") ? Request::get("p") : "home";
        $id_from_vars = Request::contains("id") ? Request::get("id") : null;
        $title = Config::SITE_NAME;
        if ($p_from_vars != "home")
            $title = CAT($title, " :: ", $p_from_vars, (!NUL($id_from_vars)? CAT(" :: ", $id_from_vars) : null));

        $Prepare->put("[#Title]", $title); //TODO -- need unique title on each page
        $Prepare->put("[#Keywords]", Config::SITE_KEYWORDS);
        $Prepare->put("[#Description]", Config::SITE_DESCRIPTION);
        $Prepare->put("[#Styles]", CAT(
                ($this->context->TestRun ? null : Config::TOP_DIR),
                $this->context->IsMobile ? "styles2" : "styles"));
        $Prepare->put("[#ContentType]", "text/html; charset=UTF-8");
        $Prepare->put("[#Top]", $engine->includeTemplate("Bula/Fetcher/Controller/Top"));
        $Prepare->put("[#Menu]", $engine->includeTemplate("Bula/Fetcher/Controller/Menu"));

        // Get included page either from cache or build it from the scratch
        $error_content = $engine->includeTemplate(CAT("Bula/Fetcher/Controller/Pages/", $class_name), "check");
        if (!BLANK($error_content)) {
            $Prepare->put("[#Page]", $error_content);
        }
        else {
            if (Config::CACHE_PAGES/* && !Config::$DontCache->contains($page_name)*/) //TODO!!!
                $Prepare->put("[#Page]", Util::showFromCache($engine, $this->context->CacheFolder, $page_name, $class_name));
            else
                $Prepare->put("[#Page]", $engine->includeTemplate(CAT("Bula/Fetcher/Controller/Pages/", $class_name)));
        }

        if (/*Config::$RssAllowed != null && */Config::SHOW_BOTTOM) {
            // Get bottom block either from cache or build it from the scratch
            if (Config::CACHE_PAGES)
                $Prepare->put("[#Bottom]", Util::showFromCache($engine, $this->context->CacheFolder, "bottom", "Bottom"));
            else
                $Prepare->put("[#Bottom]", $engine->includeTemplate("Bula/Fetcher/Controller/Bottom"));
        }

        $this->write("Bula/Fetcher/View/index.html", $Prepare);

        // Fix <title>
        //TODO -- comment for now
        //$new_title = Util::extractInfo($content, "<input type=\"hidden\" name=\"s_Title\" value=\"", "\" />");
        //if (!BLANK($new_title))
        //    $content = Regex::replace($content, "<title>(.*?)</title>", CAT("<title>", Config::SITE_NAME, " -- ", $new_title, "</title>"), RegexOptions::IgnoreCase);

        Response::write($engine->getPrintString());

        if (DBConfig::$Connection != null) {
            DBConfig::$Connection->close();
            DBConfig::$Connection = null;
        }
    }
}
