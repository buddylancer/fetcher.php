<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher;

use Bula\Objects\Request;
use Bula\Objects\Arrays;
use Bula\Objects\Strings;

use Bula\Objects\ArrayList;
use Bula\Objects\Hashtable;

use Bula\Fetcher\Controller\Engine;

require_once("Bula/Meta.php");
require_once("Bula/Fetcher/Config.php");
require_once("Bula/Objects/Request.php");
require_once("Bula/Objects/Arrays.php");
require_once("Bula/Objects/ArrayList.php");
require_once("Bula/Objects/Hashtable.php");
require_once("Bula/Objects/Strings.php");

/**
 * Class for request context.
 */
class Context extends Config {
    /** Default constructor. */
    public function __construct() {
        $this->initialize();
    }

    /** Storage for internal variables */
    protected $Values = array();

    /**
     * Get internal variable.
     * @param TString $name Name of internal variable.
     * @return TString Value of variable.
     */
    public function get($name) {
        return /*(TString)*/$this->Values[$name];
    }

    /**
     * Set internal variable.
     * @param TString $name Name of internal variable.
     * @param TString $value Value of internal variable to set.
     */
    public function set($name, $value) {
        $this->Values[$name] = $value;
    }

    /**
     * Check whether variable is contained in internal storage.
     * @param TString $name Name of internal variable.
     * @return Boolean True - variable exists, False - not exists.
     */
    public function contains($name) {
        return isset($this->Values[$name]);
    }

    /** Project root (where Bula folder is located) */
    public $LocalRoot;

    /** Host name (copied from request HOST_NAME) */
    public $Host;
    /** Site name (copied from Config::SITE_NAME) */
    public $Site;
    /** Is request for mobile version? */
    public $IsMobile;
    /** Current language */
    public $Lang;

    /** Root cache folder for pages */
    public $CacheFolderRoot;
    /** Cache folder for pages */
    public $CacheFolder;
    /** Root cache folder for output RSS-feeds */
    public $RssFolderRoot;
    /** Cache folder for output RSS-feeds */
    public $RssFolder;
    /** Cache folder for input RSS-feeds */
    public $FeedFolder;
    /** Unique host ID for current request */
    public $UniqueHostId;

    /** Use fine or full URLs */
    public $FineUrls = Config::FINE_URLS;
    /** Show an item or immediately redirect to external source item */
    public $ImmediateRedirect = Config::IMMEDIATE_REDIRECT;

    /** Storage for global constants */
    public $GlobalConstants = null;

    /** Is current request from test script? */
    public $TestRun = false;

    /**
     * Check whether current request is from test script?
     */
    public function checkTestRun() {
        $http_tester = Request::getVar(/*[Request::]*/INPUT_SERVER, "HTTP_USER_AGENT");
        if ($http_tester == null)
            return;
        if (EQ($http_tester, "TestFull")) {
            $this->TestRun = true;
            $this->FineUrls = false;
            $this->ImmediateRedirect = false;
            $this->Site = "http://www.test.com";
        }
        else if (EQ($http_tester, "TestFine")) {
            $this->TestRun = true;
            $this->FineUrls = true;
            $this->ImmediateRedirect = false;
            $this->Site = "http://www.test.com";
        }
        else if (EQ($http_tester, "TestDirect")) {
            $this->TestRun = true;
            $this->FineUrls = true;
            $this->ImmediateRedirect = true;
            $this->Site = "http://www.test.com";
        }
    }

    /**
     * Initialize all variables for current request.
     */
    public function initialize() {
        //------------------------------------------------------------------------------
        // You can change something below this line if you know what are you doing :)
        $root_dir = Request::getVar(/*[Request::]*/INPUT_SERVER, "DOCUMENT_ROOT");
        for ($n = 0; $n <= 3; $n++) {
            $last_slash_index = $root_dir->lastIndexOf("/");
            $root_dir = $root_dir->substring(0, $last_slash_index);
        }
        $this->LocalRoot = $root_dir->concat("/");
        set_include_path($this->LocalRoot->getValue());

        $this->Host = Request::getVar(/*[Request::]*/INPUT_SERVER, "HTTP_HOST");
        $this->Site = Strings::concat("http://", $this->Host);
        $this->IsMobile = $this->Host->indexOf("m.") == 0;
        $this->Lang = $this->Host->lastIndexOf(".ru") != -1 ? "ru" : "en";

        $this->checkTestRun();
        $this->UniqueHostId = Strings::concat(
            $this->IsMobile ? "mob_" : "www_",
            $this->FineUrls ? ($this->ImmediateRedirect ? "direct_" : "fine_") : "full_",
            $this->Lang);
        $this->CacheFolderRoot = Strings::concat($this->LocalRoot, "local/cache/www");
        $this->CacheFolder = Strings::concat($this->CacheFolderRoot, "/", $this->UniqueHostId);
        $this->RssFolderRoot = Strings::concat($this->LocalRoot, "local/cache/rss");
        $this->RssFolder = Strings::concat($this->RssFolderRoot, "/", $this->UniqueHostId);
        $this->FeedFolder = Strings::concat($this->LocalRoot, "local/cache/feed");

        $this->defineConstants();
    }

    /**
     * Define global constants.
     */
    private function defineConstants() {
        $this->GlobalConstants = new Hashtable();
        $this->GlobalConstants->put("[#Site_Name]", Config::SITE_NAME);
        $this->GlobalConstants->put("[#Site_Comments]", Config::SITE_COMMENTS);
        $this->GlobalConstants->put("[#Top_Dir]", Config::TOP_DIR);
        $this->GlobalConstants->put("[#Index_Page]", Config::INDEX_PAGE);
        $this->GlobalConstants->put("[#Action_Page]", Config::ACTION_PAGE);
        //if ($this->IsMobile)
        //    $this->GlobalConstants->put("[#Is_Mobile]", "1");
        $this->GlobalConstants->put("[#Lang]", $this->Lang);

//if php
        $prefix = "Bula\Fetcher\Config";
        if (defined("$prefix::NAME_CATEGORY")) $this->Set("Name_Category", Config::NAME_CATEGORY);
        if (defined("$prefix::NAME_CATEGORIES")) $this->Set("Name_Categories", Config::NAME_CATEGORIES);
        if (defined("$prefix::NAME_CREATOR")) $this->Set("Name_Creator", Config::NAME_CREATOR);
        if (defined("$prefix::NAME_CUSTOM1")) $this->Set("Name_Custom1", Config::NAME_CUSTOM1);
        if (defined("$prefix::NAME_CUSTOM2")) $this->Set("Name_Custom2", Config::NAME_CUSTOM2);

        // Map custom names
        $this->GlobalConstants->put("[#Name_Item]", Config::NAME_ITEM);
        $this->GlobalConstants->put("[#Name_Items]", Config::NAME_ITEMS);
        if ($this->contains("Name_Category"))
            $this->GlobalConstants->put("[#Name_Category]", $this->get("Name_Category"));
        if ($this->contains("Name_Categories"))
            $this->GlobalConstants->put("[#Name_Categories]", $this->get("Name_Categories"));
        if ($this->contains("Name_Creator"))
            $this->GlobalConstants->put("[#Name_Creator]", $this->get("Name_Creator"));
        if ($this->contains("Name_Custom1"))
            $this->GlobalConstants->put("[#Name_Custom1]", $this->get("Name_Custom1"));
        if ($this->contains("Name_Custom2"))
            $this->GlobalConstants->put("[#Name_Custom2]", $this->get("Name_Custom2"));
    }

    private $EngineInstances = null;
    private $EngineIndex = -1;

    /**
     * Push engine.
     * @param Boolean $print_flag Whether to print content immediately (true) or save it for further processing (false).
     */
    public function pushEngine($print_flag) {
        $engine = new Engine($this);
        $engine->setPrintFlag($print_flag);
        $this->EngineIndex++;
        if ($this->EngineInstances == null)
            $this->EngineInstances = new ArrayList();
        if ($this->EngineInstances->count() <= $this->EngineIndex)
            $this->EngineInstances->add($engine);
        else
            $this->EngineInstances->set($this->EngineIndex, $engine);
        return $engine;
    }

    /** Pop engine back. */
    public function popEngine() {
        if ($this->EngineIndex == -1)
            return;
        $engine = /*(Engine)*/$this->EngineInstances->get($this->EngineIndex);
        $engine->setPrintString(null);
        //TODO Dispose engine?
        $this->EngineIndex--;
    }

    /** Get current engine */
    public function getEngine() {
        return /*(Engine)*/$this->EngineInstances->get($this->EngineIndex);
    }
}
