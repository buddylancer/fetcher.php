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

use Bula\Objects\Arrays;
use Bula\Objects\DateTimes;
use Bula\Objects\TEnumerator;
use Bula\Objects\THashtable;
use Bula\Objects\Helper;
use Bula\Objects\Logger;
use Bula\Objects\TRequest;
use Bula\Objects\Strings;
use Bula\Objects\TString;

use Bula\Model\DBConfig;
use Bula\Model\DataSet;

use Bula\Fetcher\Model\DOCategory;
use Bula\Fetcher\Model\DOSource;
use Bula\Fetcher\Model\DOItem;
use Bula\Fetcher\Controller\Actions\DoCleanCache;

require_once("Bula/Internal.php");
require_once("Bula/Objects/Arrays.php");
require_once("Bula/Objects/DateTimes.php");
require_once("Bula/Objects/TEnumerator.php");
require_once("Bula/Objects/Helper.php");
require_once("Bula/Objects/THashtable.php");
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
class BOFetcher
{
    private $context = null;
    private $oLogger = null;
    private $dsCategories = null;

    /** Public default constructor */
    public function __construct($context)
    {
        $this->context = $context;
        $this->initializeLog();
        $this->preLoadCategories();
    }

    /**
     * Initialize logging.
     */
    private function initializeLog()
    {
        $this->oLogger = new Logger();
        $this->context->set("Log_Object", $this->oLogger);
        $log = $this->context->Request->getOptionalInteger("log");
        if (!NUL($log) && $log != -99999) { //TODO
            $filenameTemplate = new TString(CAT($this->context->LocalRoot, "local/logs/{0}_{1}.html"));
            $filename = Util::formatString($filenameTemplate, ARR("fetch_items", DateTimes::format(DateTimes::LOG_DTS)));
            $this->oLogger->initFile($filename);
        }
        else
            $this->oLogger->initTResponse($this->context->Response);
    }

    /**
     * Pre-load categories into DataSet.
     */
    private function preLoadCategories()
    {
        $doCategory = new DOCategory();
        $this->dsCategories = $doCategory->enumCategories();
    }

    /**
     * Fetch data from the source.
     * @param THashtable $oSource Source object.
     * @return Object[] Resulting items.
     */
    private function fetchFromSource($oSource)
    {
        $url = $oSource->get("s_Feed");
        if ($url->isEmpty())
            return null;

        $source = $oSource->get("s_SourceName");
        if ($this->context->Request->contains("m") && !$source->equals($this->context->Request->get("m")))
            return null;

        $this->oLogger->output(CAT("<br/>", EOL, "Started "));

        //if ($url->indexOf("https") != -1) {
        //    $encUrl = $url->replace("?", "%3F");
        //    $encUrl = $encUrl->replace("&", "%26");
        //    $url = Strings::concat(Config::$Site, "/get_ssl_rss.php?url=", $encUrl);
        //}
        $this->oLogger->output(CAT("[[[", $url, "]]]<br/>", EOL));
        $rss = Internal::fetchRss($url->getValue());
        if ($rss == null) {
            $this->oLogger->output(CAT("-- problems --<br/>", EOL));
            //$problems++;
            //if ($problems == 5) {
            //    $this->oLogger->output(CAT("<br/>", EOL, "Too many problems... Stopped.<br/>", EOL));
            //    break;
            //}
            return null;
        }
        return $rss->items;
    }

    /**
     * Parse data from the item.
     * @param THashtable $oSource Source object.
     * @param THashtable $item Item object.
     * @return Integer Result of executing SQL-query.
     */
    private function parseItemData($oSource, $item)
    {
        // Load original values

        $sourceName = $oSource->get("s_SourceName");
        $sourceId = INT($oSource->get("i_SourceId"));
        $boItem = new BOItem($sourceName, $item);
        $pubDate = $item->get("pubdate");
        $date = DateTimes::gmtFormat(DateTimes::SQL_DTS, DateTimes::fromRss($pubDate));

        // Check whether item with the same link exists already
        $doItem = new DOItem();
        $dsItems = $doItem->findItemByLink($boItem->link, $sourceId);
        if ($dsItems->getSize() > 0)
            return 0;

        $boItem->processDescription();
        //$boItem->processCustomFields(); // Uncomment for processing custom fields
        $boItem->processCategory();
        $boItem->processCreator();

        // Try to add/embed standard categories from description
        $boItem->addStandardCategories($this->dsCategories, $this->context->Lang);

        $url = $boItem->getUrlTitle(true); //TODO -- Need to pass true if transliteration is required
        $fields = new THashtable();
        $fields->put("s_Link", $boItem->link);
        $fields->put("s_Title", $boItem->title);
        $fields->put("s_FullTitle", $boItem->fullTitle);
        $fields->put("s_Url", $url);
        if ($boItem->description != null)
            $fields->put("t_Description", $boItem->description);
        if ($boItem->fullDescription != null)
            $fields->put("t_FullDescription", $boItem->fullDescription);
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
    public function fetchFromSources()
    {
        $this->oLogger->output(CAT("Start logging<br/>", EOL));

        //TODO -- Purge old items
        //$doItem = new DOItem();
        //$doItem->purgeOldItems(10);

        define("MAGPIE_CACHE_ON", true);
        define("MAGPIE_OUTPUT_ENCODING", "UTF-8");
        define("MAGPIE_DEBUG", 1);
        define("MAGPIE_FETCH_TIME_OUT", 30);
        define("MAGPIE_CACHE_DIR", CAT($this->context->FeedFolder));
        $doSource = new DOSource();
        $dsSources = $doSource->enumFetchedSources();

        $totalCounter = 0;
        $this->oLogger->output(CAT("<br/>", EOL, "Checking ", $dsSources->getSize(), " sources..."));

        // Loop through sources
        for ($n = 0; $n < $dsSources->getSize(); $n++) {
            $oSource = $dsSources->getRow($n);

            $itemsArray = $this->fetchFromSource($oSource);
            if ($itemsArray == null)
                continue;

            // Fetch done for this source
            $this->oLogger->output(" fetched ");

            $itemsCounter = 0;
            // Loop through fetched items and parse their data
            for ($i = SIZE($itemsArray) - 1; $i >= 0; $i--) {
                $hash = Arrays::createTHashtable($itemsArray[$i]);
                if (BLANK($hash->get("link")))
                    continue;
                $itemid = $this->parseItemData($oSource, $hash);
                if ($itemid > 0) {
                    $itemsCounter++;
                    $totalCounter++;
                }
            }

            // Release connection after each source
            if (DBConfig::$Connection != null) {
                DBConfig::$Connection->close();
                DBConfig::$Connection = null;
            }

            $this->oLogger->output(CAT(" (", $itemsCounter, " items) end<br/>", EOL));
        }

        // Re-count categories
        $this->recountCategories();

        $this->oLogger->output(CAT("<hr/>Total items added - ", $totalCounter, "<br/>", EOL));

        if (Config::CACHE_PAGES && $totalCounter > 0) {
            $doCleanCache = new DoCleanCache($this->context);
            $doCleanCache->cleanCache($this->oLogger);
        }
    }

    /**
     * Execute re-counting of categories.
     */
    private function recountCategories()
    {
        $this->oLogger->output(CAT("Recount categories ... <br/>", EOL));
        $doCategory = new DOCategory();
        $dsCategories = $doCategory->enumCategories();
        for ($n = 0; $n < $dsCategories->getSize(); $n++) {
            $oCategory = $dsCategories->getRow($n);
            $id = $oCategory->get("s_CatId");
            $filter = $oCategory->get("s_Filter");
            $doItem = new DOItem();
            $sqlFilter = $doItem->buildSqlFilter($filter);
            $dsItems = $doItem->enumIds($sqlFilter);
            $fields = new THashtable();
            $fields->put("i_Counter", $dsItems->getSize());
            $result = $doCategory->updateById($id, $fields);
            if ($result < 0)
                $this->oLogger->output(CAT("-- problems --<br/>", EOL));
        }
        $this->oLogger->output(CAT(" ... Done<br/>", EOL));
    }
}
