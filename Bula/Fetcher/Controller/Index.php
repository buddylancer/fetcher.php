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
class Index extends Page
{
    private static $pagesArray = null;

    private static function initialize()
    {
        self::$pagesArray = array(
            // page name,   class,          post,   code
            "home",         "Home",         0,      0,
            "items",        "Items",        0,      0,
            "view_item",    "ViewItem",     0,      0,
            "sources",      "Sources",      0,      0
        );
    }

    /** Execute main logic for Index block */
    public function execute()
    {
        if (self::$pagesArray == null)
            self::initialize();

        DataAccess::setErrorDelegate("Bula\Objects\Response::end");

        $pageInfo = Request::testPage(self::$pagesArray, "home");

        // Test action name
        if (!$pageInfo->containsKey("page")) {
            Response::end("Error in parameters -- no page");
            return;
        }

        $pageName = $pageInfo->get("page");
        $className = $pageInfo->get("class");

        Request::initialize();
        if (INT($pageInfo->get("post_required")) == 1)
            Request::extractPostVars();
        else
            Request::extractAllVars();
        //echo "In Index -- " . print_r($this, true);
        $this->context->set("Page", $pageName);

        $engine = $this->context->pushEngine(true);

        $prepare = new Hashtable();
        $prepare->put("[#Site_Name]", Config::SITE_NAME);
        $pFromVars = Request::contains("p") ? Request::get("p") : "home";
        $idFromVars = Request::contains("id") ? Request::get("id") : null;
        $title = Config::SITE_NAME;
        if ($pFromVars != "home")
            $title = CAT($title, " :: ", $pFromVars, (!NUL($idFromVars)? CAT(" :: ", $idFromVars) : null));

        $prepare->put("[#Title]", $title); //TODO -- need unique title on each page
        $prepare->put("[#Keywords]", Config::SITE_KEYWORDS);
        $prepare->put("[#Description]", Config::SITE_DESCRIPTION);
        $prepare->put("[#Styles]", CAT(
                ($this->context->TestRun ? null : Config::TOP_DIR),
                $this->context->IsMobile ? "styles2" : "styles"));
        $prepare->put("[#ContentType]", "text/html; charset=UTF-8");
        $prepare->put("[#Top]", $engine->includeTemplate("Bula/Fetcher/Controller/Top"));
        $prepare->put("[#Menu]", $engine->includeTemplate("Bula/Fetcher/Controller/Menu"));

        // Get included page either from cache or build it from the scratch
        $errorContent = $engine->includeTemplate(CAT("Bula/Fetcher/Controller/Pages/", $className), "check");
        if (!BLANK($errorContent)) {
            $prepare->put("[#Page]", $errorContent);
        }
        else {
            if (Config::CACHE_PAGES/* && !Config::$DontCache->contains($pageName)*/) //TODO!!!
                $prepare->put("[#Page]", Util::showFromCache($engine, $this->context->CacheFolder, $pageName, $className));
            else
                $prepare->put("[#Page]", $engine->includeTemplate(CAT("Bula/Fetcher/Controller/Pages/", $className)));
        }

        if (/*Config::$RssAllowed != null && */Config::SHOW_BOTTOM) {
            // Get bottom block either from cache or build it from the scratch
            if (Config::CACHE_PAGES)
                $prepare->put("[#Bottom]", Util::showFromCache($engine, $this->context->CacheFolder, "bottom", "Bottom"));
            else
                $prepare->put("[#Bottom]", $engine->includeTemplate("Bula/Fetcher/Controller/Bottom"));
        }

        $this->write("Bula/Fetcher/View/index.html", $prepare);

        // Fix <title>
        //TODO -- comment for now
        //$newTitle = Util::extractInfo($content, "<input type=\"hidden\" name=\"s_Title\" value=\"", "\" />");
        //if (!BLANK($newTitle))
        //    $content = Regex::replace($content, "<title>(.*?)</title>", CAT("<title>", Config::SITE_NAME, " -- ", $newTitle, "</title>"), RegexOptions::IgnoreCase);

        Response::write($engine->getPrintString());

        if (DBConfig::$Connection != null) {
            DBConfig::$Connection->close();
            DBConfig::$Connection = null;
        }
    }
}
