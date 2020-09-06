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

use Bula\Objects\Arrays;
use Bula\Objects\DateTimes;
use Bula\Objects\Helper;
use Bula\Objects\TString;
use Bula\Objects\Strings;

use Bula\Objects\Enumerator;
use Bula\Objects\Hashtable;

use Bula\Model\DBConfig;
use Bula\Model\DataSet;

use Bula\Objects\Logger;
use Bula\Fetcher\Model\DOCategory;
use Bula\Fetcher\Model\DOSource;
use Bula\Fetcher\Model\DOItem;
use Bula\Fetcher\Controller\Actions\DoCleanCache;

require_once("Bula/Internal.php");
require_once("Bula/Objects/Arrays.php");
require_once("Bula/Objects/DateTimes.php");
require_once("Bula/Objects/Enumerator.php");
require_once("Bula/Objects/Helper.php");
require_once("Bula/Objects/Hashtable.php");
require_once("Bula/Objects/Logger.php");
require_once("Bula/Objects/TString.php");
require_once("Bula/Objects/Strings.php");
require_once("Bula/Fetcher/Controller/BOItem.php");
require_once("Bula/Fetcher/Controller/rss/rss_fetch.inc");
require_once("Bula/Fetcher/Controller/Util.php");
require_once("Bula/Fetcher/Model/DOItem.php");
require_once("Bula/Fetcher/Model/DOCategory.php");
require_once("Bula/Fetcher/Model/DOSource.php");
require_once("Bula/Fetcher/Controller/Actions/DoCleanCache.php");

/**
 * Logic for fetching data.
 */
class BOFetcher {
    private $context = null;
    private $oLogger = null;
    private $dsCategories = null;

    /** Public default constructor */
    public function __construct($context) {
        $this->context = $context;
        $this->initializeLog();
        $this->preLoadCategories();
    }

    /**
     * Initialize logging.
     */
    private function initializeLog() {
        $this->oLogger = new Logger();
        $this->context->set("Log_Object", $this->oLogger);
        $log = Request::getOptionalInteger("log");
        if (!NUL($log) && $log != -99999) { //TODO
            $filename_template = new TString(CAT($this->context->LocalRoot, "local/logs/{0}_{1}.html"));
            $filename = Util::formatString($filename_template, ARR("fetch_items", DateTimes::format(Config::LOG_DTS)));
            $this->oLogger->init($filename);
        }
    }

    /**
     * Pre-load categories into DataSet.
     */
    private function preLoadCategories() {
        $doCategory = new DOCategory();
        $this->dsCategories = $doCategory->enumCategories();
    }

    /**
     * Fetch data from the source.
     * @param Hashtable $oSource Source object.
     * @return Object[] Resulting items.
     */
    private function fetchFromSource($oSource) {
        $url = $oSource->get("s_Feed");
        if ($url->isEmpty())
            return null;

        $source = $oSource->get("s_SourceName");
        if (Request::contains("m") && !$source->equals(Request::get("m")))
            return null;

        $this->oLogger->output("<br/>\r\nStarted ");

        //if ($url->indexOf("https") != -1) {
        //    $enc_url = $url->replace("?", "%3F");
        //    $enc_url = $enc_url->replace("&", "%26");
        //    $url = Strings::concat(Config::$Site, "/get_ssl_rss.php?url=", $enc_url);
        //}
        $this->oLogger->output(CAT("[[[", $url, "]]]<br/>\r\n"));
        $rss = Internal::fetchRss($url->getValue());
        if ($rss == null) {
            $this->oLogger->output("-- problems --<br/>\r\n");
            //$problems++;
            //if ($problems == 5) {
            //    $this->oLogger->output("<br/>\r\nToo many problems... Stopped.<br/>\r\n");
            //    break;
            //}
            return null;
        }
        return $rss->items;
    }

    /**
     * Parse data from the item.
     * @param Hashtable $oSource Source object.
     * @param Hashtable $item Item object.
     * @return Integer Result of executing SQL-query.
     */
    private function parseItemData($oSource, $item) {
        // Load original values

        $source_name = $oSource->get("s_SourceName");
        $source_id = INT($oSource->get("i_SourceId"));
        $boItem = new BOItem($source_name, $item);
        $pubdate = $item->get("pubdate");
        $date = DateTimes::format(Config::SQL_DTS, DateTimes::fromRss($pubdate));

        // Check whether item with the same link exists already
        $doItem = new DOItem();
        $dsItems = $doItem->findItemByLink($boItem->link, $source_id);
        if ($dsItems->getSize() > 0)
            return 0;

        $boItem->processDescription();
        //$boItem->processCustomFields(); // Uncomment for processing custom fields
        $boItem->processCategory();
        $boItem->processCreator();

        // Try to add/embed standard categories from description
        $boItem->addStandardCategories($this->dsCategories, $this->context->Lang);

        $url = $boItem->getUrlTitle(true); //TODO -- Need to pass true if transliteration is required
        $fields = new Hashtable();
        $fields->put("s_Link", $boItem->link);
        $fields->put("s_Title", $boItem->title);
        $fields->put("s_FullTitle", $boItem->full_title);
        $fields->put("s_Url", $url);
        if ($boItem->description != null)
            $fields->put("t_Description", $boItem->description);
        if ($boItem->full_description != null)
            $fields->put("t_FullDescription", $boItem->full_description);
        $fields->put("d_Date", $date);
        $fields->put("i_SourceLink", INT($oSource->get("i_SourceId")));
        if (!BLANK($boItem->category))
            $fields->put("s_Category", $boItem->category);
        if (!BLANK($boItem->creator))
            $fields->put("s_Creator", $boItem->creator);
        if (!BLANK($boItem->custom1))
            $fields->put("s_Custom1", $boItem->custom1);
        if (!BLANK($boItem->custom2))
            $fields->put("s_Custom2", $boItem->custom2);

        $result = $doItem->insert($fields);
        return $result;
    }

    /**
     * Main logic.
     */
    public function fetchFromSources() {
        $this->oLogger->output("Start logging<br/>\r\n");

        //TODO -- Purge old items
        //$doItem = new DOItem();
        //$doItem->purgeOldItems(10);

//if php
        define("MAGPIE_CACHE_ON", true);
        define("MAGPIE_OUTPUT_ENCODING", "UTF-8");
        define("MAGPIE_DEBUG", 1);
        define("MAGPIE_FETCH_TIME_OUT", 30);
        define("MAGPIE_CACHE_DIR", CAT($this->context->FeedFolder));
        $doSource = new DOSource();
        $dsSources = $doSource->enumFetchedSources();

        $total_counter = 0;
        $this->oLogger->output(CAT("<br/>\r\nChecking ", $dsSources->getSize(), " sources..."));

        // Loop through sources
        for ($n = 0; $n < $dsSources->getSize(); $n++) {
            $oSource = $dsSources->getRow($n);

            $items_array = $this->fetchFromSource($oSource);
            if ($items_array == null)
                continue;

            // Fetch done for this source
            $this->oLogger->output(" fetched ");

            $items_counter = 0;
            // Loop through fetched items and parse their data
            for ($i = SIZE($items_array) - 1; $i >= 0; $i--) {
                $hash = Arrays::createHashtable($items_array[$i]);
                if (BLANK($hash->get("link")))
                    continue;
                $itemid = $this->parseItemData($oSource, $hash);
                if ($itemid > 0) {
                    $items_counter++;
                    $total_counter++;
                }
            }

            // Release connection after each source
            if (DBConfig::$Connection != null) {
                DBConfig::$Connection->close();
                DBConfig::$Connection = null;
            }

            $this->oLogger->output(CAT(" (", $items_counter, " items) end<br/>\r\n"));
        }

        // Re-count categories
        $this->recountCategories();

        $this->oLogger->output(CAT("<hr/>Total items added - ", $total_counter, "<br/>\r\n"));

        if (Config::CACHE_PAGES && $total_counter > 0) {
            $doCleanCache = new DoCleanCache($this->context);
            $doCleanCache->cleanCache($this->oLogger);
        }
    }

    /**
     * Execute re-counting of categories.
     */
    private function recountCategories() {
        $this->oLogger->output(CAT("Recount categories ... <br/>\r\n"));
        $doCategory = new DOCategory();
        $dsCategories = $doCategory->enumCategories();
        for ($n = 0; $n < $dsCategories->getSize(); $n++) {
            $oCategory = $dsCategories->getRow($n);
            $id = $oCategory->get("s_CatId");
            $filter = $oCategory->get("s_Filter");
            $doItem = new DOItem();
            $sql_filter = $doItem->buildSqlFilter($filter);
            $dsItems = $doItem->enumIds($sql_filter);
            $fields = new Hashtable();
            $fields->put("i_Counter", $dsItems->getSize());
            $result = $doCategory->updateById($id, $fields);
            if ($result < 0)
                $this->oLogger->output("-- problems --<br/>\r\n");
        }
        $this->oLogger->output(CAT(" ... Done<br/>\r\n"));
    }
}
