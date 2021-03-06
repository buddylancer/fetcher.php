<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020-2021 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Fetcher;

use Bula\Objects\TRequest;
use Bula\Objects\TResponse;
use Bula\Objects\Arrays;
use Bula\Objects\Strings;

use Bula\Objects\TArrayList;
use Bula\Objects\THashtable;

use Bula\Fetcher\Controller\Engine;

use Bula\Model\Connection;

require_once("Bula/Meta.php");

require_once("Bula/Fetcher/Config.php");

require_once("Bula/Objects/Arrays.php");
require_once("Bula/Objects/TArrayList.php");
require_once("Bula/Objects/THashtable.php");
require_once("Bula/Objects/TRequest.php");
require_once("Bula/Objects/TResponse.php");
require_once("Bula/Objects/Strings.php");

require_once("Bula/Model/Connection.php");

/**
 * Class for request context.
 */
class Context extends Config
{

    /**
     * Constructor for injecting TRequest and TResponse.
     * @param Object $request Current request.
     * @param Object $response Current response.
     */
    public function __construct(Object $request= null, Object $response= null)
    {
        $this->Request = new TRequest($request);
        $this->Response = new TResponse($response);
        $this->Request->response = $this->Response;

        $this->Connection = Connection::createConnection();

        $this->initialize();
    }

    /** Public desctructor */
    public function __desctruct()
    {
        if ($this->Connection != null) {
            $this->Connection->close();
            $this->Connection = null;
        }
    }

    /** Current DB connection */
    public $Connection = null;
    /** Current request */
    public $Request = null;
    /** Current response */
    public $Response = null;

    /** Storage for internal variables */
    protected $Values = array();

    /**
     * Get internal variable.
     * @param TString $name Name of internal variable.
     * @return TString Value of variable.
     */
    public function get($name)
    {
        return $this->Values[$name];
    }

    /**
     * Set internal variable.
     * @param TString $name Name of internal variable.
     * @param TString $value Value of internal variable to set.
     */
    public function set($name, $value)
    {
        $this->Values[$name] = $value;
    }

    /**
     * Check whether variable is contained in internal storage.
     * @param TString $name Name of internal variable.
     * @return Boolean True - variable exists, False - not exists.
     */
    public function contains($name)
    {
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
    /** Optional -- API used. Currently can be blank for HTML or "rest" (for REST API) */
    public $Api;
    /** Current language */
    public $Lang;
    /** Current file extension */
    /* Filename extension */
    const FILE_EXT = ".php";

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
    public function checkTestRun()
    {
        $httpTester = $this->Request->getVar(/*[TRequest::]*/INPUT_SERVER, "HTTP_USER_AGENT");
        if ($httpTester == null)
            return;
        if (EQ($httpTester, "TestFull")) {
            $this->TestRun = true;
            $this->FineUrls = false;
            $this->ImmediateRedirect = false;
            //$this->Site = "http://www.test.com";
        }
        else if (EQ($httpTester, "TestFine")) {
            $this->TestRun = true;
            $this->FineUrls = true;
            $this->ImmediateRedirect = false;
            //$this->Site = "http://www.test.com";
        }
        else if (EQ($httpTester, "TestDirect")) {
            $this->TestRun = true;
            $this->FineUrls = true;
            $this->ImmediateRedirect = true;
            //$this->Site = "http://www.test.com";
        }
    }

    /**
     * Initialize all variables for current request.
     */
    public function initialize()
    {
        //------------------------------------------------------------------------------
        // You can change something below this line if you know what are you doing :)
        $rootDir = $this->Request->getVar(/*[$TRequest->]*/INPUT_SERVER, "DOCUMENT_ROOT");
        $rootDir = $rootDir->replace("\\", "/"); // Fix for IIS
        $removeSlashes =
            2;
        // Regarding that we have the ordinary local website (not virtual directory)
        for ($n = 0; $n <= $removeSlashes; $n++) {
            $lastSlashIndex = $rootDir->lastIndexOf("/");
            $rootDir = $rootDir->substring(0, $lastSlashIndex);
        }
        $this->LocalRoot = $rootDir->concat("/");
        set_include_path($this->LocalRoot->getValue());

        $this->Host = $this->Request->getVar(/*[TRequest::]*/INPUT_SERVER, "HTTP_HOST");
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
    private function defineConstants()
    {
        $this->GlobalConstants = new THashtable();
        $this->GlobalConstants->put("[#Site_Name]", Config::SITE_NAME);
        $this->GlobalConstants->put("[#Site_Comments]", Config::SITE_COMMENTS);
        $this->GlobalConstants->put("[#Top_Dir]", Config::TOP_DIR);

        if (!$this->TestRun)
            $this->GlobalConstants->put("[#File_Ext]", self::FILE_EXT);
        $this->GlobalConstants->put("[#Index_Page]", $this->TestRun ? Config::INDEX_PAGE :
            Strings::replace("[#File_Ext]", self::FILE_EXT, Config::INDEX_PAGE));
        $this->GlobalConstants->put("[#Action_Page]", $this->TestRun ? Config::ACTION_PAGE :
            Strings::replace("[#File_Ext]", self::FILE_EXT, Config::ACTION_PAGE));
        $this->GlobalConstants->put("[#Rss_Page]", $this->TestRun ? Config::RSS_PAGE :
            Strings::replace("[#File_Ext]", self::FILE_EXT, Config::RSS_PAGE));

        //if ($this->IsMobile)
        //    $this->GlobalConstants->put("[#Is_Mobile]", "1");
        $this->GlobalConstants->put("[#Lang]", $this->Lang);

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
     * @param Boolean $printFlag Whether to print content immediately (true) or save it for further processing (false).
     * @return Engine New Engine instance.
     */
    public function pushEngine($printFlag)
    {
        $engine = new Engine($this);
        $engine->setPrintFlag($printFlag);
        $this->EngineIndex++;
        if ($this->EngineInstances == null)
            $this->EngineInstances = new TArrayList();
        if ($this->EngineInstances->size() <= $this->EngineIndex)
            $this->EngineInstances->add($engine);
        else
            $this->EngineInstances->set($this->EngineIndex, $engine);
        return $engine;
    }

    /** Pop engine back. */
    public function popEngine()
    {
        if ($this->EngineIndex == -1)
            return;
        $engine = $this->EngineInstances->get($this->EngineIndex);
        $engine->setPrintString(null);
        //TODO Dispose engine?
        $this->EngineIndex--;
    }

    /**
     * Get current engine
     * @return Engine Current engine instance.
     */
    public function getEngine()
    {
        return $this->EngineInstances->get($this->EngineIndex);
    }
}
