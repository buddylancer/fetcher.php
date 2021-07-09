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
use Bula\Fetcher\Model\DOItem;
use Bula\Fetcher\Model\DOMapping;
use Bula\Fetcher\Model\DORule;
use Bula\Fetcher\Model\DOSource;
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
require_once("Bula/Fetcher/Model/DOCategory.php");
require_once("Bula/Fetcher/Model/DOItem.php");
require_once("Bula/Fetcher/Model/DOMapping.php");
require_once("Bula/Fetcher/Model/DORule.php");
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
    private $dsRules = null;
    private $dsMappings = null;

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
            $this->oLogger->initResponse($this->context->Response);
    }

    /**
     * Pre-load categories into DataSet.
     */
    private function preLoadCategories()
    {
        $doCategory = new DOCategory();
        $this->dsCategories = $doCategory->enumCategories();
        $doRule = new DORule();
        $this->dsRules = $doRule->enumAll();
        $doMapping = new DOMapping();
        $this->dsMappings = $doMapping->enumAll();
    }

    /**
     * Fetch data from the source.
     * @param THashtable $oSource Source object.
     * @return Object[] Resulting items.
     * @param TString $from Addition to feed URL (for testing purposes)
     */
    private function fetchFromSource($oSource, $from)
    {
        $url = $oSource->get("s_Feed");
        if ($url->isEmpty())
            return null;

        if (!NUL($from))
            $url = Strings::concat($url, "&from=", $from);

        if ($url->indexOf("[#File_Ext]") != -1)
            $url = $url->replace("[#File_Ext]", Context::FILE_EXT);

        $source = $oSource->get("s_SourceName");
        if ($this->context->Request->contains("m") && !$source->equals($this->context->Request->get("m")))
            return null;

        $this->oLogger->output(CAT("<br/>", EOL, "Started "));

        //if ($url->indexOf("https") != -1) {
        //    $encUrl = $url->replace("?", "%3F");
        //    $encUrl = $encUrl->replace("&", "%26");
        //    $url = Strings::concat(Config::$Site, "/get_ssl_rss.php?url=", $encUrl);
        //}
        $this->oLogger->output(CAT("[[[", $url, "]]]"));
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
        if (BLANK($pubDate) && !BLANK($item->get("dc"))) { //TODO implement [dc][time]
            $temp = $item->get("dc");
            if (!BLANK($temp->get("date"))) {
                $pubDate = $temp->get("date");
                $item->put("pubDate", $pubDate);
            }
        }

        $boItem->processMappings($this->dsMappings);

        $boItem->processDescription();
        //$boItem->processCustomFields(); // Uncomment for processing custom fields
        $boItem->processCategory();
        $boItem->processCreator();

        // Process rules AFTER processing description (as some info can be extracted from it)
        $boItem->processRules($this->dsRules);

        if (BLANK($boItem->link)) //TODO - what we can do else?
            return 0;

        // Get date here as it can be extracted in rules processing
        if ($boItem->date != null)
            $pubDate = $boItem->date;
        if (!BLANK($pubDate))
            $pubDate = $pubDate->trim();
        $date = DateTimes::gmtFormat(DateTimes::SQL_DTS, DateTimes::fromRss($pubDate));

        // Check whether item with the same link exists already
        $doItem = new DOItem();
        $dsItems = $doItem->findItemByLink($boItem->link, $sourceId);
        if ($dsItems->getSize() > 0)
            return 0;

        // Try to add/embed standard categories from description
        $countCategories = $boItem->addStandardCategories($this->dsCategories, $this->context->Lang);

        $boItem->normalizeCategories();

        // Check the link once again after processing rules
        if ($dsItems == null && !BLANK($boItem->link)) {
            $doItem->findItemByLink($boItem->link, $sourceId);
            if ($dsItems->getSize() > 0)
                return 0;
        }

        $url = $boItem->getUrlTitle(true); //TODO -- Need to pass true if transliteration is required
        $fields = new THashtable();
        $fields->put("s_Link", $boItem->link);
        $fields->put("s_Title", $boItem->title);
        $fields->put("s_FullTitle", $boItem->fullTitle);
        $fields->put("s_Url", $url);
        $fields->put("i_Categories", $countCategories);
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
     * @param TString $from Addition to feed URL (for testing purposes)
     */
    public function fetchFromSources($from)
    {
        $this->oLogger->output(CAT("Start logging<br/>", EOL));

        //TODO -- Purge old items
        //$doItem = new DOItem();
        //$doItem->purgeOldItems(10);

        define("MAGPIE_OUTPUT_ENCODING", "UTF-8");
        define("MAGPIE_DEBUG", 1);
        define("MAGPIE_FETCH_TIME_OUT", 30);
        //if (!$this->context->TestRun) {
        //    define("MAGPIE_CACHE_ON", true);
        //    define("MAGPIE_CACHE_DIR", CAT($this->context->FeedFolder));
        //}
        //else {
            define("MAGPIE_CACHE_ON", false);
        //}
        $doSource = new DOSource();
        $dsSources = $doSource->enumFetchedSources();

        $totalCounter = 0;
        $this->oLogger->output(CAT("<br/>", EOL, "Checking ", $dsSources->getSize(), " sources..."));

        // Loop through sources
        for ($n = 0; $n < $dsSources->getSize(); $n++) {
            $oSource = $dsSources->getRow($n);

            $itemsArray = $this->fetchFromSource($oSource, $from);
            if ($itemsArray == null)
                continue;

            // Fetch done for this source
            //$this->oLogger->output(" fetched ");

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

            $this->oLogger->output(CAT("<br/>", EOL, "... fetched (", $itemsCounter, " items) end"));
        }

        // Re-count categories
        $this->recountCategories();

        $this->oLogger->output(CAT("<br/>", EOL, "<hr/>Total items added - ", $totalCounter, "<br/>", EOL));

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
        $this->oLogger->output(CAT("<br/>", EOL, "Recount categories ... "));
        $doCategory = new DOCategory();
        $doItem = new DOItem();
        $dsCategories = $doCategory->enumCategories();
        for ($n = 0; $n < $dsCategories->getSize(); $n++) {
            $oCategory = $dsCategories->getRow($n);
            $categoryId = $oCategory->get("s_CatId");
            $oldCounter = INT($oCategory->get("i_Counter"));

            //$filter = $oCategory->get("s_Filter");
            //$sqlFilter = DOItem::buildSqlByFilter($filter);

            $categoryName = $oCategory->get("s_Name");
            $sqlFilter = DOItem::buildSqlByCategory($categoryName);

            $dsCounters = $doItem->enumIds(CAT("_this.b_Counted = 0 AND ", $sqlFilter));
            if ($dsCounters->getSize() == 0)
                continue;

            $newCounter = INT($dsCounters->getSize());

            //Update category
            $categoryFields = new THashtable();
            $categoryFields->put("i_Counter", $oldCounter + $newCounter);
            $doCategory->updateById($categoryId, $categoryFields);
        }

        $doItem->update("_this.b_Counted = 1", "_this.b_Counted = 0");

        $this->oLogger->output(CAT(" ... Done<br/>", EOL));
    }
}
